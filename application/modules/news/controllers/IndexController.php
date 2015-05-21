<?php

/**
 * News_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_IndexController extends MF_Controller_Action {
 
    public static $articleItemCountPerPage = 12;
    
    public function indexAction() {
        
    }
    
    public function lastNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $lastNews = $newsService->getLastNews(6,Doctrine_Core::HYDRATE_ARRAY);
        $this->view->assign('lastNews', $lastNews);
        $this->_helper->viewRenderer->setResponseSegment('lastNews');
    }
    
    public function breakingNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        
        $breakingNews = $newsService->getBreakingNews(Doctrine_Core::HYDRATE_ARRAY);
        $this->view->assign('breakingNews', $breakingNews);
        
        
        $this->_helper->viewRenderer->setResponseSegment('breakingNews');
    }
    
    public function lastCategoriesNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $categoryService = $this->_service->getService('News_Service_Category');
        
        
        $categories = $categoryService->getAllCategories();
        $newsList = array();
        foreach($categories as $category):
            if($category['title'] == "Reportaże")
                continue;
            $newsList[$category['title']] = $newsService->getLastCategoryNews($category['id'],6,Doctrine_Core::HYDRATE_ARRAY);
        endforeach;
        
        $this->view->assign('newsList', $newsList);
        
        
        $this->_helper->viewRenderer->setResponseSegment('lastCategoriesNews');
    }
    
    public function lastNewsSidebarAction() {
        $newsService = $this->_service->getService('News_Service_News');
        
        $lastNewsSidebar = $newsService->getLastNews(3,Doctrine_Core::HYDRATE_ARRAY);
        
        $this->view->assign('lastNewsSidebar', $lastNewsSidebar);
        
        
        $this->_helper->viewRenderer->setResponseSegment('lastNewsSidebar');
    }
    
    public function listNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        
        $newsList = $newsService->getAllNews();
        
        
         $query = $newsService->getNewsPaginationQuery($category['id'],$this->language);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$articleItemCountPerPage);
        
        $this->view->assign('newsList', $newsList);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
    
    public function listNewsCategoryAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $categoryService = $this->_service->getService('News_Service_Category');
        
        
        if(!$category = $categoryService->getCategory($this->getRequest()->getParam('category'), 'slug',  Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        
       $newsList = $newsService->getLastCategoryNews($category['id'],null,Doctrine_Core::HYDRATE_ARRAY);
        
        
        $this->view->assign('category', $category);
        $this->view->assign('newsList', $newsList);
        
        $this->_helper->layout->setLayout('article');
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        
    }
    
    
    
    public function categoryAction(){
        
        $this->_helper->layout->setLayout('article');
        $this->_helper->actionStack('layout', 'index', 'default');
        
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $newsService = $this->_service->getService('News_Service_News');
        $categoryService = $this->_service->getService('News_Service_Category');
        
        if(!$category = $categoryService->getCategory($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Category not found', 404);
        }
        
        if(!$page = $pageService->getPage($this->getRequest()->getParam('slug'), 'type')) {
            
        }
        
         $query = $newsService->getCategoryPaginationQuery($category['id'],$this->language);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$articleItemCountPerPage);
        
        $this->view->assign('paginator', $paginator);
       
        
        $metatagService->setViewMetatags($page['metatag_id'],$this->view);
        
        
         $this->view->assign('paginator', $paginator);
         $this->view->assign('category', $category);
        
    }
    
    public function searchAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
       
        $search = $this->getRequest()->getParam('search_name');
        $search = $this->view->escape($search);
        $searchResults = $newsService->findNews($search,Doctrine_Core::HYDRATE_ARRAY);
            
         if(!$page = $pageService->getPage('wyniki-wyszukiwania', 'type')) {
            
        }
        
        $metatagService->setViewMetatags($page['metatag_id'],$this->view);
        
        $this->view->assign('newsList', $searchResults);
        $this->view->assign('search', $search);
        
        $this->_helper->layout->setLayout('article');
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        
    }
    
    public function groupAction(){
        
        $this->_helper->layout->setLayout('article');
        $this->_helper->actionStack('layout', 'index', 'default');
        
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $newsService = $this->_service->getService('News_Service_News');
        $groupService = $this->_service->getService('News_Service_Group');
        
        if(!$group = $groupService->getGroup($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Group not found', 404);
        }
        
        if(!$page = $pageService->getPage($this->getRequest()->getParam('slug'), 'type')) {
            
        }
        
        $metatagService->setViewMetatags($page['metatag_id'],$this->view);
        
          $query = $newsService->getGroupPaginationQuery($group['id'],$this->language);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$articleItemCountPerPage);
        
        $this->view->assign('paginator', $paginator);
        
         $this->view->assign('newsList', $newsList);
         $this->view->assign('group', $group);
        
    }
    
    public function tagAction(){
        
        $this->_helper->layout->setLayout('article');
        $this->_helper->actionStack('layout', 'index', 'default');
        
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $newsService = $this->_service->getService('News_Service_News');
        $tagService = $this->_service->getService('News_Service_Tag');
        
        if(!$tag = $tagService->getTag($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Tag not found', 404);
        }
        
        
        $metatagService->setViewMetatags($tag['metatag_id'],$this->view);
        
        $newsList = $newsService->getTagNews($tag['id'],Doctrine_Core::HYDRATE_ARRAY);
        
         $this->view->assign('newsList', $newsList);
         $this->view->assign('tag', $tag);
        
    }
    
     public function studentAction(){
        
        $this->_helper->layout->setLayout('article');
        $this->_helper->actionStack('layout', 'index', 'default');
        
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $newsService = $this->_service->getService('News_Service_News');
         if(!$page = $pageService->getPage('studencka-tworczosc', 'type')) {
            
        }
        
        $metatagService->setViewMetatags($page['metatag_id'],$this->view);
                
        $newsList = $newsService->getStudentNews(Doctrine_Core::HYDRATE_ARRAY);
        
         $this->view->assign('newsList', $newsList);
        
    }
    
    public function articleAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $adService = $this->_service->getService('Banner_Service_Ad');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $commentService = $this->_service->getService('News_Service_Comment');
//        $censorService = $this->_service->getService('Censor_Service_Censor');
//        $settingsService = $this->_service->getService('Default_Service_Setting');
        if(!$article = $newsService->getFullArticle($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Article not found', 404);
        }
//        $ad = $adService->getActiveAd($article['VideoRoot']['Ad']['id']);
//        $lastServerId = $settingsService->getSetting('server',Doctrine_Core::HYDRATE_RECORD);
//        $videoUrl = $article['VideoRoot']['url'];
        // jak nie vimeo i youtube
//        if(strpos($videoUrl,'vimeo')==false && strpos($videoUrl,'youtube')==false){
//            if($lastServerId->value==1){
//                $videoUrl = str_replace('stream2', 'stream1', $videoUrl);
//                $lastServerId->value = 2;
//                $lastServerId->save();
//            }
//            elseif($lastServerId->value==2){
//                $videoUrl = str_replace('stream1', 'stream2', $videoUrl);
//                $lastServerId->value = 1;
//                $lastServerId->save();
//            }
//        }
       
        $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

        if(!$pageWasRefreshed ) {
           $article->increaseView();
        } 
        
//        $lastCategoryOtherArticles = $newsService->getLastCategoryOtherArticles($article,Doctrine_Core::HYDRATE_ARRAY);
        
//        $ipService = $this->_service->getService('Censor_Service_Ip');
        
//        $ips = $ipService->getAllIps(Doctrine_Core::HYDRATE_SINGLE_SCALAR);
        $metatagService->setViewMetatags($article->get('Metatags'), $this->view);
        $metatagService->setOgMetatags($this->view,$article['Translation'][$this->view->language]['title'],'/media/photos/'.$article['PhotoRoot']['offset']."/".$article['PhotoRoot']['filename'],$article['Translation'][$this->view->language]['content']);
       
//        $comments = $commentService->getNewsComments($article['id'],Doctrine_Core::HYDRATE_ARRAY);
//        $comments_count = $commentService->countNewsComments($article['id'],Doctrine_Core::HYDRATE_SINGLE_SCALAR);
        $this->view->assign('article', $article);
//        $this->view->assign('comments', $comments);
//        $this->view->assign('comment_count', $comments_count);
        
//        $form = new Default_Form_Contact();
//        $form->removeElement('firstName');
//        $form->removeElement('lastName');
//        $form->removeElement('email');
//        $form->removeElement('subject');
//        $form->removeElement('message');
//        $form->removeElement('csrf');
//        $captchaDir = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('captchaDir');
//        $form->addElement('captcha', 'captcha',
//            array(
//            'label' => 'Rewrite the chars', 
//            'captcha' => array(
//                'captcha' => 'Image',  
//                'wordLen' => 5,  
//                'timeout' => 300,  
//                'font' => APPLICATION_PATH . '/../data/arial.ttf',  
//                'imgDir' => $captchaDir,  
//                'imgUrl' => $this->view->serverUrl() . '/captcha/',  
//            )
//        ));
//        
//        if(isset($_POST['submit_comment'])){
//            if(in_array($_SERVER['REMOTE_ADDR'],$ips)){
//            $this->view->messages()->add('Twój adres IP został zablokowany','error');
//        }elseif($form->isValid($this->getRequest()->getPost())){
//            $values = $_POST;
//            
//            if(!$censorService->checkCensor($values['content'])){
//                $this->view->messages()->add('Ten komentarz zawiera niecenzuralne słowa','error');
//            }
//            else{
//                
//            $values['news_id'] = $article['id'];
//            $commentService->addComment($values,$article['id']);
//                $this->view->messages()->add('Komentarz dodany pomyślnie');
//                        
//            return $this->_helper->redirector->goToUrl($this->view->url(array('slug' => $article['Translation'][$this->view->language]['slug']),'domain-news-article')); 
//        
//            }
//            }
//            else{
//                $this->view->messages()->add('Podany kod jest niepoprawny','error');
//            }
//        }
//        
//       $this->view->assign('videoUrl',$videoUrl);
//       $this->view->assign('ad',$ad);
//       $this->view->assign('form',$form);
//       $this->view->assign('lastCategoryOtherArticles',$lastCategoryOtherArticles);
        
       
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
    
    
     public function streamAction() {
        $streamService = $this->_service->getService('News_Service_Stream');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        if(!$stream = $streamService->getFullStream($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Stream not found', 404);
        }
       
        $metatagService->setViewMetatags($stream->get('Metatags'), $this->view);
        $metatagService->setOgMetatags($this->view,$stream['Translation'][$this->view->language]['title'],'',$stream['Translation'][$this->view->language]['content']);
       
        
        $this->view->assign('stream', $stream);
        
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
    
    public function facebookAction(){
        
    }
    
}

