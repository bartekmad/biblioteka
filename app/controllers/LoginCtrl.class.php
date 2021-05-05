<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\RoleUtils;
use app\forms\LoginForm;

class LoginCtrl{
	private $form;
	
	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->form = new LoginForm();
	}
		
	public function validate() {
		$this->form->login = ParamUtils::getFromRequest('login');
		$this->form->pass = ParamUtils::getFromRequest('pass');

		//nie ma sensu walidować dalej, gdy brak parametrów
		if (!isset($this->form->login)) return false;
		
		// sprawdzenie, czy potrzebne wartości zostały przekazane
		if (empty($this->form->login)) {
			App::getMessages()->addMessage(new Message('Nie podano loginu', Message::ERROR));
		}
		if (empty($this->form->pass)) {
			App::getMessages()->addMessage(new Message('Nie podano hasła', Message::ERROR));
		}

		//nie ma sensu walidować dalej, gdy brak wartości
		if (App::getMessages()->isError()) return false;
		
		// sprawdzenie, czy dane logowania poprawne
		// (takie informacje najczęściej przechowuje się w bazie danych)
		$dbUser = App::getDB()->select("UZYTKOWNIK", "*",["login" => $this->form->login]);
		if (count($dbUser)>0 && ($this->form->pass == $dbUser[0]['haslo'])) {
			RoleUtils::addRole($dbUser[0]['id_uprawnienia']);
			SessionUtils::store("user", $dbUser[0]);
		} else {
			App::getMessages()->addMessage(new Message('Niepoprawny login lub hasło', Message::ERROR));
		}
		
		return ! App::getMessages()->isError();
	}

	public function action_loginShow(){
		$this->generateView(); 
	}
	
	public function action_login(){
		if ($this->validate()){
			//zalogowany => przekieruj na główną akcję (z przekazaniem messages przez sesję)
			App::getMessages()->addMessage(new Message('Poprawnie zalogowano do systemu', Message::INFO));
			if (RoleUtils::inRole("0")) {
				App::getRouter()->redirectTo("przegladanieKsiazek");
			}
			else if (RoleUtils::inRole("1")) {
				App::getRouter()->redirectTo("przegladanieKsiazek");
			}
                        else if (RoleUtils::inRole("2")) {
				App::getRouter()->redirectTo("przegladanieKsiazek");
			}
		} else {
			//niezalogowany => pozostań na stronie logowania
			$this->generateView(); 
		}		
	}
	
	public function action_logout(){
		// 1. zakończenie sesji
		session_destroy();
		// 2. idź na stronę główną - system automatycznie przekieruje do strony logowania
		App::getRouter()->redirectTo('login');
	}	
	
	public function generateView(){
                App::getSmarty()->assign('page_title','panel logowania');
		App::getSmarty()->assign('form',$this->form); // dane formularza do widoku
		App::getSmarty()->display('LoginView.tpl');		
	}
}