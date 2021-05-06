<?php namespace app\controllers;

use app\forms\UzytkownicyForm;
use core\App;
use core\SessionUtils;
use core\Message;
use core\ParamUtils;
use PDOException;

class UzytkownicyCtrl {
    
    private $form;
    private $listaUzytkownikow;
    private $czyAdmin;
    private $listaUprawnien;
    private $czyZalogowany;
    private $poziomUprawnien;
    
    public function __construct()
    {
        $this->form = new UzytkownicyForm();
        $this->listaUzytkownikow;
    }
    
    public function action_panelUzytkownikow()
    {
        $this->generujWidok();
    }
    
    public function action_zarzadzajUzytkownikami()
    {
        $this->ustawOperacje();
        if ($this->form->operacja == 1)
            $this->pobierzListeUzytkownikow();
        $this->generujWidok();
    }
    
    private function ustawOperacje()
    {
        $this->form->operacja = ParamUtils::getFromRequest('operacja',true,'Błędne wywołanie aplikacji');
    }
    
    private function pobierzListeUzytkownikow()
    {
        $this->czyAdmin = 0 == intval(SessionUtils::load("user",true)['id_uprawnienia']);
        if ($this->czyAdmin == true)
            $this->listaUzytkownikow = App::getDB()->select("UZYTKOWNIK", ["id_uzytkownika","login"]);
        else 
            $this->listaUzytkownikow = App::getDB()->select("UZYTKOWNIK", ["id_uzytkownika","login"], ["id_uprawnienia[>]"=>0]);
    }
    
    public function action_dodajUzytkownika()
    {
        $this->pobierzParametryDodawania();
        if ($this->czyWpisaneWartosciDodawania())
        {
            if ($this->walidujDodawanieUzytkownika())
                $this->zapiszDaneNaBazeDodawanie();
        }
        $this->generujWidok();   
    }
    
    private function pobierzParametryDodawania()
    {
        $this->form->login = ParamUtils::getFromRequest('login',true,'Błędne wywołanie aplikacji');
        $this->form->haslo = ParamUtils::getFromRequest('haslo',true,'Błędne wywołanie aplikacji');
        $this->form->imie = ParamUtils::getFromRequest('imie',true,'Błędne wywołanie aplikacji');
        $this->form->nazwisko = ParamUtils::getFromRequest('nazwisko',true,'Błędne wywołanie aplikacji');
    }
    
    private function pobierzParametryEdycji()
    {
        $this->form->uzytkownik = ParamUtils::getFromRequest('uzytkownik',true,'Błędne wywołanie aplikacji');
        $this->form->haslo = ParamUtils::getFromRequest('haslo',true,'Błędne wywołanie aplikacji');
    }
    
    private function walidujDodawanieUzytkownika()
    {
        $walidacja = true;

        if ($this->form->login == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano loginu użytkownika!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->haslo == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano hasła użytkownika!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->imie == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano imienia!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->nazwisko == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano hasła nazwiska!', Message::ERROR));
            $walidacja = false;
        }
        
        $wynik = App::getDB()->select("UZYTKOWNIK",[
            "login",
            ],
            [
            "login"=>$this->form->login
            ]
        );
        if (count($wynik) > 0)
        {
            foreach($wynik as $dana)
            {
                if ($this->form->login == $dana["login"])
                {
                    App::getMessages()->addMessage(new Message('Użytkownik o podanym loginie istnieje w bazie!', Message::ERROR));
                    $walidacja = false;
                }
            }
        }
        
        $wynik = App::getDB()->select("UZYTKOWNIK",[
            "imie",
            "nazwisko"
            ],
            [
            "imie"=>$this->form->imie,
            "nazwisko"=>$this->form->nazwisko
            ]
        );
        if (count($wynik) > 0)
        {
            foreach($wynik as $dana)
            {
                if ($this->form->login == $dana["imie"] && $this->form->login == $dana["nazwisko"])
                {
                    App::getMessages()->addMessage(new Message('Użytkownik o podanym imieniu oraz nazwisku istnieje w bazie!', Message::ERROR));
                    $walidacja = false;
                }
            }
        }
        
        return $walidacja;
    }
    
    private function zapiszDaneNaBazeDodawanie()
    {
        $idUprawnienia = 2;
        if (isset($this->form->uprawnienia))
            $idUprawnienia = intval($this->form->uprawnienia);
        try
        {
            App::getDB()->insert("UZYTKOWNIK", [
                "login" => strval($this->form->login),
                "haslo" => strval($this->form->haslo),
                "imie" => strval($this->form->imie),
                "nazwisko" => strval($this->form->nazwisko),
                "id_uprawnienia" => $idUprawnienia
            ]);
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas dodawania użytkownika.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie dodano użytkownika.', Message::INFO));
        }
    }
    
    private function czyWpisaneWartosciDodawania()
    {
        return (isset($this->form->login) && isset($this->form->haslo) && isset($this->form->imie) && isset($this->form->nazwisko));
    }
    
    public function action_edytujUzytkownika()
    {
        $this->pobierzParametryEdycji();
        if ($this->czyWpisaneWartosciEdytowania())
        {
            if ($this->walidujEdytowanieUzytkownika())
                $this->zapiszDaneNaBazeEdycja();
        }
        $this->generujWidok();   
    }
    
    private function czyWpisaneWartosciEdytowania()
    {
        return (isset($this->form->uzytkownik) && isset($this->form->haslo));
    }
    
    private function walidujEdytowanieUzytkownika()
    {
        $walidacja = true;

        if ($this->form->uzytkownik == "")
        {
            App::getMessages()->addMessage(new Message('Nie wybrano użytkownika!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->haslo == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano hasla!', Message::ERROR));
            $walidacja = false;
        }
        
        return $walidacja;
    }
    
    private function zapiszDaneNaBazeEdycja()
    {
        try
        {
            App::getDB()->update("UZYTKOWNIK", [
                "haslo" => strval($this->form->haslo)
                ],
                [
                "id_uzytkownika" => $this->form->uzytkownik
            ]);
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Próba zmiany hasła nieudana.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie zaktualizowano hasło.', Message::INFO));
        }
    }
    
    private function generujWidok()
    {
        App::getSmarty()->assign('page_title','zarządzanie użytkownikami');
        App::getSmarty()->assign('form',$this->form);
        App::getSmarty()->assign('listaUzytkownikow',$this->listaUzytkownikow);
        $this->zaladujUprawnienia();
        
        App::getSmarty()->display('uzytkownicy.tpl');
    }
    
    private function zaladujUprawnienia()
    {
        $this->czyAdmin = 0 == intval(SessionUtils::load("user",true)['id_uprawnienia']);
        App::getSmarty()->assign('czyAdmin',$this->czyAdmin);
        $this->listaUprawnien = App::getDB()->select("UPRAWNIENIA", "*");
        App::getSmarty()->assign('listaUprawnien',$this->listaUprawnien);
        $this->czyZalogowany = 0 != intval(SessionUtils::load("user",true)['id_uzytkownika']);
        App::getSmarty()->assign('czyZalogowany',$this->czyZalogowany);
        $this->poziomUprawnien = intval(SessionUtils::load("user",true)['id_uprawnienia']);
        App::getSmarty()->assign('poziomUprawnien',$this->poziomUprawnien);
    }
}
