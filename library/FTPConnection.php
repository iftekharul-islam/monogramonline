<?php namespace Monogram; 
 
use Illuminate\Support\Facades\Log; 
 
class FTPConnection 
{ 
  
  // try { 
  //     $ftp = new FTPConnection($this->url, $this->port, $this->username, $this->password); 
  //     $file_list = $ftp->downloadFiles($this->remote_download, $this->download_dir); 
  //     $ftp->close();
  //   } catch (\Exception $e) { 
  //       Log::error('FTP download error ' . $e->getMessage()); 
  //       return FALSE; 
  //   } 

  //  try { 
  //      $ftp->uploadDirectory($this->remote_upload, $this->upload_dir);
  //  } catch (\Exception $e) { 
  //      Log::error('FTP upload error ' . $e->getMessage()); 
  //      return FALSE; 
  //  } 

    private $connection; 
    private $file_list; 
    private $local_download_dir; 
    protected $local_upload_dir; 
     
    public function __construct($host, $username, $password, $pasv = FALSE) 
    {   
        if (!$this->connection = @ftp_connect($host)) {
            Log::error("FTPConnection: Could not connect to $host."); 
            throw new \Exception("Could not connect to $host.");
        }

        if (! @ftp_login($this->connection, $username, $password)) { 
            Log::error("FTPConnection login: Could not authenticate with username $username and password $password."); 
            throw new \Exception("Could not authenticate with username $username and password $password."); 
        }
        
        if ($pasv == TRUE && ! @ftp_pasv($this->connection, TRUE)) { 
            Log::error("FTPConnection login: Could not set passive mode."); 
            throw new \Exception("Could not set passive mode."); 
        }
    } 
     
    public function close() 
    {
      ftp_close($this->connection);
    }
    
    public function setFileList($remotedir)  
    { 
      try { 
         
        $this->download_dir = $remotedir;  
        $nlist = ftp_nlist($this->connection, $this->download_dir);
        $rawlist = ftp_rawlist($this->connection, $this->download_dir);
        
        $list = array();
        
        foreach ($rawlist as $i => $value) { 
            if($value[0] != 'd') {
                $list[] = str_replace([$this->download_dir, "\\"], '', $nlist[$i]);
            } 
        }
        
        $this->file_list = $list;
        
      } catch (\Exception $e) { 
        Log::error('FTPConnection setFileList: ' . $e->getMessage()); 
        throw new \Exception($e->getMessage()); 
      } 
       
    } 

    public function getFileList() 
    { 
      return $this->file_list; 
    } 
     
    public function setLocalDir ($direction, $localdir) 
    { 
      if (substr($localdir,0,1) != '/') {
        $localdir = '/' . $localdir;
      }
      if (file_exists (  storage_path() . $localdir )) { 
          if ($direction == 'download') {
            $this->local_download_dir = storage_path() . $localdir; 
          } else if ($direction == 'upload') {
            $this->local_upload_dir = storage_path() . $localdir;
          } else {
            Log::error('FTPConnection setLocalDir: Unrecognized direction ' . $direction); 
            throw new \Exception('Unrecognized direction - setLocalDir'); 
          }
      } else { 
        Log::error('FTPConnection setLocalDir: Local Directory not found ' . storage_path() . $localdir); 
        throw new \Exception('Local Directory not found ' . storage_path() . $localdir); 
      } 
    } 
     
   public function downloadFiles($remotedir, $localdir) 
   { 
     $this->setFileList($remotedir);
     $this->setLocalDir('download', $localdir); 

     foreach ($this->file_list as $file) { 
  
       Log::info("FTPConnection downloadFiles: Copying Remote file $file"); 
       
       if (!ftp_get($this->connection, $this->local_download_dir . $file, $this->download_dir . $file, FTP_BINARY)) {
        Log::error("FTPConnection downloadFiles: Error copying Remote file $file"); 
        throw new \Exception("Could not Copy Remote file $file");
       }
        
       if (!@ftp_delete($this->connection, $this->download_dir . $file)) { 
         Log::error("FTPConnection downloadFiles: Could not Delete Remote file $file"); 
         throw new \Exception("Could not Delete Remote file $file"); 
       } 
     }
     
     return $this->file_list;
   } 
  
   public function uploadDirectory($remote_dir, $localdir) 
   {
     $this->setLocalDir('upload', $localdir);
     $file_list = array_diff(scandir($this->local_upload_dir), array('..', '.')); 
     
     foreach ($file_list as $filename) { 
        
         if (!@ftp_put($this->connection, $remote_dir . $filename, $this->local_upload_dir . $filename, FTP_ASCII)) { 
             Log::error("FTPConnection uploadFile: Could not send data from file: $filename"); 
             throw new \Exception("Could not send data from file: $filename"); 
         } 
        
         Log::info("FTPConnection uploadFile:  Local file $filename uploaded"); 
     } 
     
     return $file_list;
   }

} 
