<?php namespace Monogram; 
 
use Illuminate\Support\Facades\Log; 
 
class SFTPConnection 
{ 
  
  // try { 
  //    
  //     $sftp = new SFTPConnection($this->url, $this->port); 
  //     $sftp->login($this->username, $this->password);  
  //     $sftp->downloadFiles($this->remote_download, $this->download_dir); 
  //      
  //     $file_list = $sftp->getFileList();  
  //     $dir = $sftp->getLocalDir('download'); 
  //      
  //   } catch (\Exception $e) { 
  //       Log::error('Walmart getFiles: SFTP download error ' . $e->getMessage()); 
  //       return FALSE; 
  //   } 

  //  try { 
  //      $sftp->uploadFiles($upload_files, $this->remote_upload, $this->upload_dir); 
  //  } catch (\Exception $e) { 
  //      Log::error('Walmart getFiles: SFTP upload error ' . $e->getMessage()); 
  //      return FALSE; 
  //  } 

    private $connection; 
    private $sftp; 
    private $connection_string; 
    private $file_list; 
    private $download_dir; 
    private $local_download_dir; 
    private $local_upload_dir; 
     
    public function __construct($host, $port = 22) 
    {   
        $this->connection = @ssh2_connect($host, $port);
          
        if (! $this->connection) { 
            Log::error("SFTPConnection: Could not connect to $host on port $port."); 
            throw new \Exception("Could not connect to $host on port $port."); 
        } 
 
    } 
 
 
    public function login($username, $password = null, $public = null, $private = null) 
    {   
        if ($public == null) {
          if (! @ssh2_auth_password($this->connection, $username, $password)) { 
              Log::error("SFTPConnection login: Could not authenticate with username $username and password $password."); 
              throw new \Exception("Could not authenticate with username $username and password $password."); 
          } 
        } else {
          if (! @ssh2_auth_pubkey_file($this->connection, $username, $public, $private)) {
              Log::error("SFTPConnection login: Could not authenticate with username $username and public key $public."); 
              throw new \Exception("Could not authenticate with username $username and public key $public."); 
          }
        }
        
        $this->sftp = @ssh2_sftp($this->connection); 
         
        if (! $this->sftp) { 
            Log::error("SFTPConnection login: Could not initialize SFTP subsystem."); 
            throw new \Exception("Could not initialize SFTP subsystem."); 
        } 
         
        $this->connection_string = 'ssh2.sftp://' . intval($this->sftp); 
         
    } 
     
     
    public function setFileList($remotedir)  
    { 
      try { 
         
        $this->download_dir = $this->setSlashes($remotedir);  
        $this->file_list = array_diff(scandir($this->connection_string . $this->download_dir), array('..', '.')); 
         
      } catch (\Exception $e) { 
        Log::error('SFTPConnection setFileList: ' . $e->getMessage()); 
        throw new \Exception($e->getMessage()); 
      } 
       
    } 
     
     
    public function getFileList() 
    { 
      return $this->file_list; 
    } 
     
     
    public function setLocalDownloadDir ($localdir) 
    { 
      $localdir = $this->setSlashes($localdir);
      
      if (file_exists (  storage_path() . $localdir )) { 
         
        $this->local_download_dir = storage_path() . $localdir; 
         
      } else { 
        Log::error('SFTPConnection setLocalDownloadDir: Local Download Directory not found ' . storage_path() . $localdir); 
        throw new \Exception('Local Download Directory not found ' . storage_path() . $localdir); 
      } 
    } 
     
     
    public function setLocalUploadDir ($localdir) 
    { 
      $localdir = $this->setSlashes($localdir);
      
      if (file_exists (  storage_path() . $localdir )) { 
         
        $this->local_upload_dir = storage_path() .  $localdir; 
         
      } else { 
        Log::error('SFTPConnection setLocalUploadDir: Local Upload Directory not found ' . storage_path() . $localdir); 
        throw new \Exception('Local Upload Directory not found ' . storage_path() . $localdir); 
      } 
    } 
    
    
   public function getLocalDir($direction) 
   { 
     if ($direction == 'download') { 
       return $this->local_download_dir; 
     } else if ($direction == 'upload') { 
       return $this->local_upload_dir; 
     } else { 
       Log::error("SFTPConnection getLocalDir: Direction $direction not recognized."); 
       throw new \Exception("Direction $direction not recognized."); 
     } 
   } 
    
    
   public function downloadFiles($remotedir, $localdir, $retain = null, $ignore = null) 
   { 
     $this->setFileList($remotedir);
     $this->setLocalDownloadDir($localdir); 
      
      
     foreach ($this->file_list as $file) { 
        
        if ($file == $ignore) {
          continue;
        }
        
        Log::info("SFTPConnection downloadFiles: Copying Remote file $file"); 
      
       //  ssh2_scp_recv($this->connection,  $this->download_dir . $file, $this->local_download_dir . $file);
  
        $filesize = filesize($this->connection_string . $this->download_dir . $file); 
        
        if ($filesize != 0) {
          
          if (!$stream = @fopen($this->connection_string . $this->download_dir . $file, 'r')) { 
              Log::error("SFTPConnection downloadFiles: Unable to open remote file: $file"); 
              throw new \Exception("Unable to open remote file"); 
          } 
          
          if (!$local = @fopen($this->local_download_dir . $file, 'w')) 
          { 
              Log::error("SFTPConnection downloadFiles: Unable to create local file: $file"); 
              throw new \Exception("Unable to create local file: " . $this->local_download_dir . $file); 
          } 
          
          $read = 0; 
                  
          while ($read < $filesize && ($buffer = fread($stream, $filesize - $read))) 
          { 
              $read += strlen($buffer); 
              if (fwrite($local, $buffer) === FALSE) 
              { 
                  Log::error("SFTPConnection downloadFiles: Unable to write to local file: $file"); 
                  throw new \Exception("Unable to write to local file: $file"); 
              } 
          } 
          
          fclose($local); 
          fclose($stream); 
          
        } else {
        
          // Log::error("SFTPConnection downloadFiles: Remote file size zero $file"); 
          // throw new \Exception("Remote file size zero: " . $file); 
          
          try {
            $input = file_get_contents($this->connection_string . $this->download_dir . $file);
            file_put_contents($this->local_download_dir . $file, $input);
          } catch (\Exception $e) {
            Log::error("SFTPConnection downloadFiles: could not read $file"); 
            throw new \Exception("Could not read: " . $file);
          }
        }
        
        if ($retain == null) {
            if (unlink($this->connection_string . $this->download_dir . $file)) { 
              Log::info("SFTPConnection downloadFiles: Deleted Remote file $file"); 
            } else { 
              Log::info("SFTPConnection downloadFiles: Could not Delete Remote file $file"); 
              throw new \Exception("Could not Delete Remote file $file"); 
            } 
        } 
      
     } 
     
     return $this->file_list;
   } 
  
   public function uploadDirectory($remote_dir, $localdir) 
   {
     
     $this->upload_dir = $this->setSlashes($remote_dir);  
     $this->setLocalUploadDir($localdir);
     
     $file_list = array_diff(scandir($this->local_upload_dir), array('..', '.')); 
     $this->uploadFiles($file_list);
     
     return $file_list;
   }
    
   public function uploadFiles($filenames = null, $remote_dir = null, $localdir = null) 
   {   
       if ($remote_dir != null) {
         $this->upload_dir = $this->setSlashes($remote_dir);
       }
       
       if  ($localdir != null) {
         $this->setLocalUploadDir($localdir);
       }
       
       foreach ($filenames as $filename) { 
            
           $stream = @fopen($this->connection_string . $this->upload_dir . $filename, 'w'); 
           
           if (! $stream) { 
               Log::error("SFTPConnection uploadFile: Could not open file: " . $this->connection_string . $this->upload_dir . $filename); 
               throw new \Exception("Could not open remote file: " . $this->connection_string . $this->upload_dir . $filename); 
           } 

           $data_to_send = @file_get_contents($this->local_upload_dir . $filename); 
            
           if ($data_to_send === false) { 
               Log::error("SFTPConnection uploadFile: Could not open local file: " . $this->local_upload_dir . $filename); 
               throw new \Exception("Could not open local file: " . $this->local_upload_dir . $filename); 
           } 
          
           if (@fwrite($stream, $data_to_send) === false) { 
               Log::error("SFTPConnection uploadFile: Could not send data from file: $filename"); 
               throw new \Exception("Could not send data from file: $filename"); 
           } 
          
           @fclose($stream); 
            
           Log::info("SFTPConnection uploadFile:  Local file $filename uploaded"); 
       } 
       
   } 
   
   private function setSlashes ($dir)
   {
     if ($dir[0] != '/') {
       $dir = '/' . $dir;
     }
     
     if (substr($dir, -1) != '/') {
       $dir = $dir . '/';
     }
     
     return $dir;
   }
} 
