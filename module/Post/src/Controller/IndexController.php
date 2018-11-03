<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Post\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use RuntimeException ;

use MongoDB;
use MongoDB\Driver\Manager;

use MongoDB\Driver\Query;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    
        $collection = $this->getCollection();
        $posts = $collection->find();
       
        // return new ViewModel();
        // $view =new ViewModel();
        // $view->setTemplate('post/head.phtml');
        $contentView = new ViewModel(array('posts'=>$posts));
        $contentView->setTemplate('post/index.phtml'); // path to phtml file under view folder
        // $bottomView = new ViewModel();
        // $bottomView->setTemplate('post/bottom.phtml');
        // $view->addChild($contentView)
        //         ->addChild($bottomView);
        return $contentView;
    }

    public function addAction()
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $username = $request->getPost('email');
            $name = $request->getPost('name');
            $password = password_hash($request->getPost('pwd'),PASSWORD_DEFAULT);
            $collection = $this->getCollection();
            $result = $collection->insertOne( [ 'name' => $name, 'username' => $username,'password'=>$password ] );
            return $this->redirect()->toRoute('post');
        }
        else{
            $contentView = new ViewModel();
            $contentView->setTemplate('post/create.phtml');
            // $id = $this->params()->fromRoute('id');
            return $contentView;
        }
        
        
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $collection = $this->getCollection();
        $post = $collection->findOne(["_id"=> new MongoDB\BSON\ObjectId("$id")]);
        $contentView = new ViewModel(["post"=>$post]);
        $contentView->setTemplate('post/update.phtml');
            // $id = $this->params()->fromRoute('id');
        return $contentView;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $collection = $this->getCollection();
        $collection->deleteOne(array('_id' =>  new MongoDB\BSON\ObjectId("$id")));
        return $this->redirect()->toRoute('post');
    }

    public function updateAction(){
        $id = $this->params()->fromRoute('id');
        $request = $this->getRequest();
        $collection = $this->getCollection();
        $post = $collection->findOne(["_id"=> new MongoDB\BSON\ObjectId("$id")]);
        if(password_verify($request->getPost('opwd'),$post["password"])){
            $username = $request->getPost('email');
            $name = $request->getPost('name');
            $password = password_hash($request->getPost('npwd'),PASSWORD_DEFAULT); 
            $newData = array('$set'=>array("username"=>$username,"password"=>$password,"name"=>$name));
            $collection->updateOne(["_id"=>new MongoDB\BSON\ObjectId("$id")],$newData);
            return $this->redirect()->toRoute('post');
        }
        
        $contentView = new ViewModel(["post"=>$post,"error"=>"Old Password is Wrong!"]);
        $contentView->setTemplate('post/update.phtml');
            // $id = $this->params()->fromRoute('id');
        return $contentView;
    }

    public function getCollection(){
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $collection = $client->crud->post;
        return $collection;
    }
}
