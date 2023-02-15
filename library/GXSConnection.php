<?php namespace Monogram; 
 
use Illuminate\Support\Facades\Log; 
use Monogram\SFTPConnection;

class GXSConnection 
{
    protected $host = 'sftp.tradinggrid.gxs.com';
    
    protected $username = 'AAH44237';
    protected $password = 'C5B472XF';
    
    // protected $remote_download = '/AAH44237/POLLABLE/';
    protected $remote_download = '/AAH44237/';
    protected $download_dir = 'EDI/GXS/download/'; 
    
    protected $remote_upload = '/AAH44237/';
    protected $upload_dir = '/EDI/GXS/upload/'; 
    
    public function getFiles() 
    {   
      Log::info('GXS: contacting via SFTP');
      
      try { 
          $sftp = new SFTPConnection($this->host, 22); 
          $sftp->login($this->username, $this->password);   
      } catch (\Exception $e) { 
          Log::error('GXS getFiles: SFTP connection error ' . $e->getMessage()); 
          return FALSE; 
      } 
       
      try { 
          $files = $sftp->downloadFiles($this->remote_download, $this->download_dir);
      } catch (\Exception $e) { 
          Log::error('GXS getFiles: SFTP download error ' . $e->getMessage()); 
          return FALSE; 
      } 
      
      return true;
    } 
    
    public function sendFiles() 
    {   
         try { 
           
           $sftp = new SFTPConnection($this->host, 22); 
           $sftp->login($this->username, $this->password);  

         } catch (\Exception $e) { 
            Log::error('GXS sendFiles: SFTP connection error ' . $e->getMessage()); 
            return FALSE; 
         }
         
         try { 
           
           $files = $sftp->uploadDirectory($this->remote_upload, $this->upload_dir); 

         } catch (\Exception $e) { 
            Log::error('GXS sendFiles: SFTP upload error ' . $e->getMessage()); 
            return FALSE; 
         }
         
         foreach ($files as $file) {
           try {
             unlink(storage_path() . $this->upload_dir . $file);
           } catch (\Exception $e) {
             Log::error('GSX: cannot delete upload ' . $file);
             return FALSE;
           }
         }
         
         return true;
   } 
} 
