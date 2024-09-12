<?php

namespace Monogram;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use \Imagick;

class ImageHelper
{
    public static function getImageSize($file, $scale = 100)
    {

        $type = substr($file, strrpos($file, '.'));

        $width = null;
        $height = null;

        if(!is_int($scale)) {
            if(is_string($scale)) {
                $scale = intval($scale);
            } else {
                $scale = (int)$scale;
            }
        }
        $s = $scale / 100.0;

        if($type == '.eps') {

            try {
                $file_handle = fopen($file, "r");

                if(!$file_handle) {
                    Log::error('ImageHelper getImageSize: File open failed. ' . $file);
                }

                while (!feof($file_handle)) {
                    $line = fgets($file_handle);
                    if(strpos($line, 'HiResBoundingBox') !== false) {
                        $line = str_replace("\r\n", '', $line);
                        $ex = explode(' ', $line);
                        $width = number_format((intval(trim($ex[3])) * $s) / 72, 3);
                        $height = number_format((intval(trim($ex[4])) * $s) / 72, 3);
                        break;
                    }
                }
                fclose($file_handle);

            } catch (Exception $e) {
                Log::error('ImageHelper getImageSize: Error opening EPS file - ' . $e->getMessage());
            }

        } else {
            if($type == '.pdf') {

                $output = shell_exec("pdfinfo " . $file);

                // find page sizes
                preg_match('/Page size:\s+([0-9]{0,5}\.?[0-9]{0,3}) x ([0-9]{0,5}\.?[0-9]{0,3})/', $output,
                    $pagesizematches);

                if($pagesizematches != []) {
                    $width = number_format(($pagesizematches[1] * $s) / 72, 3);
                    $height = number_format(($pagesizematches[2] * $s) / 72, 3);
                } else {
                    Log::error('ImageHelper getImageSize: pdfinfo failed. ' . $file);
                }

            } else {
                if($type == '.jpg' || $type == '.jpeg') {

                    $size = @getimagesize($file);

                    if(is_array($size) && isset($size[1])) {
                        $width = number_format(($size[0] * $s) / 72, 3);
                        $height = number_format(($size[1] * $s) / 72, 3);
                    } else {
                        Log::error('ImageHelper getImageSize: getimagesize failed. ' . $file);
                    }

                } else {

                    $size = @getimagesize($file);

                    if(is_array($size) && isset($size[1])) {
                        $width = number_format(($size[0] * $s) / 150, 3);
                        $height = number_format(($size[1] * $s) / 150, 3);
                    } else {
                        Log::error('ImageHelper getImageSize: getimagesize failed. ' . $file);
                    }
                }
            }
        }

        if($height != null) {
            return ['file' => $file, 'type' => $type, 'width' => $width, 'height' => $height, 'scale' => $scale];
        }

        return false;
    }

    public static function createThumb($image_path, $flop = 0, $thumb_path, $size = 250)
    {
//$image_path = "/var/www/order.monogramonline.com/public_html/media/archive/675085.pdf";
        set_time_limit(0);
        $image_path = str_replace('/media/RDrive/archive/', '/media/RDrive/archive/', $image_path);
        Log::info("BEGIN createThumb: " . $image_path);
        if(stripos($image_path, "pdf") !== false) {
            try {
//                dd("Before", $image_path);
                $image_path = str_replace("https://order.monogramonline.com/", "/var/www/5p_oms/public_html/", $image_path);
                $output1 = shell_exec("pdftoppm -scale-to 250 -jpeg -jpegopt quality=70 -r 72 $image_path " . $image_path);
                $output2 = shell_exec("mv $image_path" . "-1.jpg " . str_replace("pdf", "jpg", $thumb_path));
//                dd("After ", $image_path, $flop = 0, $thumb_path, $size, $output1, $output2);
            } catch (\Exception $e) {
                dd('1 ImageHelper createThumb: ', $e->getMessage(), $output1, $output2);
            }
        } else {
            logger("on the jpg size reduce and Image path: " . $image_path);

            // Load the image
            $image = new Imagick($image_path);
//            $image->setOption('policy', 'max-pixels', '50MP');

            // $image->trimImage(20000);
            if($flop == 1) {
                $image->flopImage();
            }
            try {
                $image->setImageAlphaChannel(Imagick::VIRTUALPIXELMETHOD_WHITE);
            } catch (\Exception $e) {
                Log::error('ImageHelper createThumb: ' . $e->getMessage());
                Log::error('ImageHelper createThumb image_path: ' . $image_path);
            }
//            $image->transformImageColorspace(Imagick::COLORSPACE_CMY);
             $image->thumbnailImage($size, $size, true);

            if(file_exists($thumb_path)) {
                try {
                    unlink($thumb_path);
                } catch (\Exception $e) {
                    Log::error('ImageHelper createThumb: failed deleting old thumb ' . $thumb_path . ' - ' . $e->getMessage());
                    return;
                }
            }
            if(!$image->writeImage($thumb_path)) {
                Log::error('ImageHelper createThumb: Error writing thumbnail ' . $image_path);
            }
        }
    }

    public static function getImageInfo($filePath)
    {
        $imageSize = null;
        $fileSize = null;
        $fileType = null;

        // Check if the file path has a JPG extension and '/media/archive/'
        if ((Str::endsWith($filePath, '.jpg') || Str::endsWith($filePath, '.pdf')) && Str::contains($filePath, '/media/archive/')) {
            // Determine the command to use based on the file extension
            $command = Str::endsWith($filePath, '.pdf') ? 'pdfinfo' : 'exiftool';

            $filename = pathinfo($filePath, PATHINFO_BASENAME);

            // Create the new path for the image based on your criteria
            $newImagePath = "/media/RDrive/archive/{$filename}";
            // Build the full command

            if(file_exists($newImagePath)){
                $fullCommand = "{$command} " . $newImagePath;

                $commandOutput = shell_exec($fullCommand);
                $info = ImageHelper::parseCommandOutput($commandOutput);

                if ($command == 'pdfinfo') {
                    $imageSize = $info['Page size'] ?? null;
                    $fileSize = $info['File size'] ?? null;
                    $fileType = 'PDF';
                } else if($command == 'exiftool') {
                    $imageWidth = null;
                    $imageHeight = null;
                    if(isset($info['Image Width']) && isset($info['X Resolution'])) {
                        $imageWidth = $info['Image Width'] / $info['X Resolution'];
                        $imageWidth = is_float($imageWidth) ? number_format($imageWidth, 2) : $imageWidth;
                    }
                    if (isset($info['Image Height']) && isset($info['Y Resolution'])) {
                        $imageHeight = $info['Image Height'] / $info['Y Resolution'];
                        $imageHeight = is_float($imageHeight) ? number_format($imageHeight, 2) : $imageHeight;
                    }
                    $imageSize = !empty($imageWidth) && !empty($imageHeight) ? $imageWidth . ' X ' . $imageHeight . ' (Inch)' : 'N/A';
                    $fileSize = $info['File Size'] ?? null;
                    $fileDPI = $info['X Resolution'] ?? null;
                    $fileType = !empty($info['File Type Extension']) ? $info['File Type Extension'] : 'N/A';
                }


                return [
                    'message' => 'Get file info successfully',
                    'status' => true,
                    'image_size' => $imageSize,
                    'image_dpi' => !empty($fileDPI) ? $fileDPI : 'N/A',
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                ];
            } else {
                return [
                    'message' => 'File is not exist',
                    'status' => false,
                    'image_size' => $imageSize,
                    'image_dpi' => null,
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                ];
            }
        }

        return [
            'message' => 'File is not in valid path',
            'status' => false,
            'image_size' => $imageSize,
            'image_dpi' => null,
            'file_size' => $fileSize,
            'file_type' => $fileType,
        ];
    }

    public static function parseCommandOutput($output)
    {
        // Initialize an array to store the extracted information
        $info = [];

        // Split the output into lines
        $lines = explode("\n", $output);

        // Iterate through each line
        foreach ($lines as $line) {
            // Split each line into key and value
            $parts = explode(':', $line, 2);

            // Trim whitespace from key and value
            $key = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            // Add the key-value pair to the info array
            $info[$key] = $value;
        }

        return $info;
    }
}
