<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        $this->render('error', array("error"=>Yii::app()->errorHandler->error));
    }
    
    /**
    * Displays the login page
    */
    public function actionLogin()
    {
        if(!Yii::app()->user->isGuest) Yii::app()->user->logout();
        $this->makeSSL();
        $model = new LoginForm;
        $redirect = (isset($_REQUEST["redirect"])) ? $_REQUEST["redirect"] : "index";
        $error = "";
        // collect user input data
        if (isset($_POST['username']) and isset($_POST["password"])) {
                $model->username = $_POST["username"];
                $model->password = $_POST["password"];
                // validate user input and redirect to the previous page if valid
                if ($model->validate() && $model->login())
                {
                    $user = new UserObj($model->username);
                    $user->login();
                    $this->redirect($redirect);
                }
            else
                $error = "We could not find you. Either you typed in your username/password wrong or you do not exist. Please exist.";
        }
        // display the login form
        $this->render('login', array('model' => $model,"error"=>$error));
    }
    
    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }
    
	public function beforeAction($action)
	{
        if(($this->getIsMobile() and is_null(Yii::app()->user->getState('mobile'))) or (Yii::app()->user->getState('mobile') === TRUE)) {
            Yii::app()->theme = 'mobile';
        }
		return $action;
	}
	
	public function actionToStandard()
	{
		Yii::app()->user->setState("mobile",false);
		$this->redirect(Yii::app()->createUrl("index"));
		exit;
	}
	
	public function actionToMobile()
	{
		Yii::app()->user->setState("mobile",true);
		$this->redirect(Yii::app()->createUrl("index"));
		exit;
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}
	
	public function actionRunOnce()
	{
		$this->renderPartial("runonce");
	}

	public function actionPlace()
	{
		$this->render('place');
	}
    
	public function actionSearch()
	{
        if(isset($_REQUEST["q"]) && !empty($_REQUEST["q"])){
    	    # Query for the places from the search (models/Functions)
            $places_return = search($_REQUEST["q"]);
            extract($places_return); # $places (obj), $search_type (string)
            
            # Save the search to the DB
            $search             = new SearchObj();
            $search->search     = $_REQUEST["q"];
            $search->ipaddress  = $_SERVER["REMOTE_ADDR"];
            // $search->results    = json_encode($places);
            $search->numresults = count($places);
            $search->save();
            
    		$this->render('search',array("places"=>$places,"search_type"=>$search_type));
        }
        else {
            $this->render('search');
        }
	}
	
    /**
     * The actions below are for AJAX calls only
     */
     public function action_load_places_by_placetype()
     {
    	$this->login_only();
        $placetypeid = $_REQUEST["placetypeid"];
        $placetype = new PlaceTypesObj($placetypeid);
        $placetype = new PlaceTypesObj($placetype->parentid);
        $placesmanager = new Places;
        $places = $placesmanager->load_places($placetype->machinecode);
        
        ob_start();
        echo "<option value='0'></option>";
        foreach($places as $place):
            echo "<option value='".$place->placeid."'>".$place->placename."</option>";
        endforeach;
        $contents = ob_get_contents();
        ob_end_clean();
        
        return print $contents;
     }


    /** Google Analytics **/
    protected function beforeRender($view)
    {
        $return = parent::beforeRender($view);
        // Yii::app()->googleAnalytics->render();
        return $return;
    }
    
	private function makeSSL()
	{
		if($_SERVER['SERVER_PORT'] != 443) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}

	private function makeNonSSL()
	{
		if($_SERVER['SERVER_PORT'] == 443) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}
	
	private function login_only()
	{
		if(Yii::app()->user->isGuest)
		{
		    $url = urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
			$this->redirect(Yii::app()->createUrl('login')."?redirect=".$url);
			exit;
		}
	}
}