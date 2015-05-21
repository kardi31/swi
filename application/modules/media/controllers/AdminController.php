<?php

class Media_AdminController extends MF_Controller_Action
{
    public function cropPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $photosDir = $photoService->photosDir;
        
        $data = Zend_Json::decode($this->getRequest()->getParam('data'));
        $id = $data['id'];
        $filename = $data['filename'];
        $offset = $data['offset'];
        $watermark = isset($data['watermark']) ? 1 : 0;
        $data = $data['data'];
        
        $response = array('status' => 'error');
        
        $offsetDir = realpath($photosDir . DIRECTORY_SEPARATOR . $offset);
        
        $cropData = array();
        foreach($data as $item) {
            $cat = $item['cat'];
            $filePath = $offsetDir . DIRECTORY_SEPARATOR . $filename;
            $x = $item['x'];
            $y = $item['y'];
            $x2 = $item['x2'];
            $y2 = $item['y2'];
            $w = $item['w'];
            $h = $item['h'];
            $cropData[$cat] = array('x' => $x, 'y' => $y, 'x2' => $x2, 'y2' => $y2, 'w' => $w, 'h' => $h);
            
            $dimensions = preg_match('/(\d*)x(\d*)/', $cat, $match);
            $width = (0 == (int) $match[1]) ? null : (int) $match[1];
            $height = (0 == (int) $match[2]) ? null : (int) $match[2];

            if(!file_exists($filePath)) {
                $this->_helper->json($response);
            }

            if(!is_dir($offsetDir . DIRECTORY_SEPARATOR . $cat)) {
                @mkdir($offsetDir . DIRECTORY_SEPARATOR . $cat);
            }
            
            $photoService->crop($filePath, $offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $filename, $x, $y, $x2, $y2, $width, $height);
            if($watermark)
                $photoService->addWatermark($offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $filename, APPLICATION_PATH . '/../data/watermark.png');
            $response['status'] = 'success';
        }
        
        if(!empty($cropData)) {
            if($photo = $photoService->getPhoto($id)) {
                $currentCropData = ($photo->getCropData()) ? $photo->getCropData() : array();
                $photo->setCropData(array_merge($currentCropData, $cropData));
                $photo->save();
            }
        }
        
        $this->_helper->json($response);
    }
    
    /**
     * elfinder 2 connect action 
     */
    public function elfinderAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinderConnector.class.php';
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinder.class.php';
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinderVolumeDriver.class.php';
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinderVolumeLocalFileSystem.class.php';
        
        $mediaDir = $this->getFrontController()->getParam('bootstrap')->getOption('mediaDir');
        $elfinderUrl = $this->getFrontController()->getParam('bootstrap')->getOption('elfinderUrl');
        $absoluteElfinderUrl = $this->getFrontController()->getParam('bootstrap')->getOption('absoluteElfinderUrl');
        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path'          => $absoluteElfinderUrl,
                    'URL'           => $elfinderUrl, // URL to files (REQUIRED)
//                    'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
                )
            )
        );
        
//        var_dump(realpath($mediaDir . '/elfinder/'));exit;
//        var_dump(realpath(APPLICATION_PATH."/../public_html/media/elfinder"));
////        var_dump($mediaDir . '/elfinder/');
//        var_dump($elfinderUrl);exit;
        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
        $connector->output();
    }
    
    /**
     * elfinder connect action for review edition
     */
    public function connectAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $options = $this->getInvokeArg('bootstrap')->getContainer()->get('elfinder');
        
        $elFinder = new elFinder($options);
        $elFinder->run();
    }
    
    /**
     * TinyMCE elfinder client
     */
    public function tinymceAction() {
        
    }   
    
    /**
     * elfinder client
     */
    public function clientAction() {
        $this->_helper->layout()->disableLayout();
    }    
    
    /**
     * elfinder client
     */
    public function client2Action() {
        $this->_helper->layout()->disableLayout();
    }    
}
