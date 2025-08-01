<?php
$logFilePath = 'C:\Users\pc\Desktop\ETS GIA\Work\log.txt';
$logFile = fopen($logFilePath, 'a');
$folderPath = 'C:\Users\pc\Desktop\ETS GIA\Work';
$files = scandir($folderPath);
$logContents = file_get_contents($logFilePath);


$procDir = 'C:\Users\pc\Desktop\ETS GIA\Proc';
$srcDir = 'C:\Users\pc\Desktop\ETS GIA\ETS Shared folder';
$destDir = 'C:\Users\pc\Desktop\ETS GIA\Work';
$badDir = 'C:\Users\pc\Desktop\ETS GIA\Bad';
$GIADir = 'C:\Users\pc\Desktop\ETS GIA\GIA';
$pattern = "/^(DHAR)\d{9}(.pdf|.jpeg)|^(D)\d{8}(.pdf|.jpeg)/";
date_default_timezone_set("Asia/Riyadh");
$date = date('dmYHis');

if (file_exists($destDir)&&file_exists($badDir)) {

  if (is_dir($destDir)&&is_dir($badDir)) {

    if (is_writable($destDir)&&is_writable($badDir)) {

      if ($handle = opendir($srcDir)) {
        
        foreach (scandir($srcDir) as $file) {

          while (false !== ($file = readdir($handle))) {

            $fileInfo = pathinfo($file);
            $ext = pathinfo($srcDir . '/' . $file,PATHINFO_EXTENSION);
            $name = pathinfo($srcDir . '/' . $file,PATHINFO_FILENAME);
            $newname = $name . "_" .$date . '.'.$ext;
            
            if (is_file($srcDir . '/' . $file) && (strtolower($fileInfo['extension']) == 'pdf' || strtolower($fileInfo['extension']) == 'jpeg') && preg_match($pattern, $file)){
            
              rename($srcDir . '/' . $file, $destDir .'/'. $newname); 
              
            }

            if(is_file($srcDir . '/' . $file)){
              rename($srcDir . '/' . $file, $badDir . '/' . $file);
            }

          }
        }
      } 
    }
  }
} 

if (file_exists($procDir)&&file_exists($badDir)) {

  if (is_dir($procDir)&&is_dir($badDir)) {

    if (is_writable($procDir)&&is_writable($badDir)) {

      if ($handle = opendir($destDir)) {
        
        $sourceFiles = scandir($destDir);
        foreach ($sourceFiles as $filename) {
          if ($filename !== "." && $filename !== "..") {
            $first14Digits = substr($filename, 0, 14);
            $targetFiles = scandir($procDir);
            $duplicate = false;
            
            foreach ($targetFiles as $targetFilename) {
              if ($targetFilename !== "." && $targetFilename !== "..") {
                $targetFirst14Digits = substr($targetFilename, 0, 14);
                if ($first14Digits === $targetFirst14Digits) {
                  $duplicate = true;
                  break;
                }
              }
            }

            if ($duplicate) {
              echo "Duplicate file found: " . $filename . "\n";
              rename($destDir . '/' . $filename, $badDir .'/'. $filename);
              $logMessage = "File: $filename is duplicated (already sent to GIA)\n";
              fwrite($logFile, $logMessage);
            } 
          }
        }
      }
    }
  }
}

if (file_exists($GIADir)&&file_exists($procDir)) {

  if (is_dir($GIADir)&&is_dir($procDir)) {

    if (is_writable($GIADir)&&is_writable($procDir)) {

      if ($handle = opendir($destDir)) {
        
        while (false !== ($file = readdir($handle))) {

          $fileInfo = pathinfo($file);
            
          if (is_file($destDir . '/' . $file) && (strtolower($fileInfo['extension']) == 'pdf' || strtolower($fileInfo['extension']) == 'jpeg')){
            rename($destDir . '/' . $file, $GIADir .'/'. $file);
            copy($GIADir . '/' . $file, $procDir .'/'. $file);
            $logMessage = "File: $file was sent to GIA\n";
            fwrite($logFile, $logMessage);
          }
        }
      }
      closedir($handle);
      echo "DONE";
    }
  }
} 
?>
