<?php
namespace Admin\Controller;

class IndexController extends EntryController
{
    public function index()
    {
        $this->redirect('Login/index');
    }
}
