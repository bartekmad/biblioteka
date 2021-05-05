<?php
namespace app\controllers;

use app\forms\AutorForm;
use core\App;
use core\SessionUtils;
use core\Message;
use core\ParamUtils;
use PDOException;

class AutorCtrl
{
    private $form;
    private $result;
    
    public function __construct()
    {
        $this->form = new AutorForm();
    }
    
    public function action_panelAutorow()
    {
        $this->result = App::getDB()->select("AUTOR", "*");
        $this->generujWidokWyswietl();
    }
    
    public function action_wyswietlDodajAutora()
    {
        $this->generujWidokDodaj();
    }
    
    public function action_dodajAutora()
    {
        $this->pobierzParametryDodawania();
        if ($this->czyWpisaneWartosciDodawania())
        {
            if ($this->walidujDodawanieAutora())
                $this->zapiszDaneNaBazeDodawanie();
        }
        $this->action_wyswietlDodajAutora();   
    }
    
    private function pobierzParametryDodawania()
    {
        $this->form->imie = ParamUtils::getFromRequest('imie',true,'Błędne wywołanie aplikacji');
        $this->form->nazwisko = ParamUtils::getFromRequest('nazwisko',true,'Błędne wywołanie aplikacji');
    }
    
    private function czyWpisaneWartosciDodawania()
    {
        return (isset($this->form->imie) && isset($this->form->nazwisko));
    }
    
        private function walidujDodawanieAutora()
    {
        $walidacja = true;

        if ($this->form->imie == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano imienia autora!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->nazwisko == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano nazwiska autora!', Message::ERROR));
            $walidacja = false;
        }
        
        $wynik = App::getDB()->select("AUTOR",[
            "imie_autora",
            "nazwisko_autora"
            ],
            [
            "imie_autora"=>$this->form->imie
            ],
            [
            "nazwisko_autora"=>$this->form->nazwisko   
            ]
        );
        if (count($wynik) > 0)
        {
            foreach($wynik as $dana)
            {
                if ($this->form->imie == $dana["imie_autora"] && $this->form->nazwisko == $dana["nazwisko_autora"])
                {
                    App::getMessages()->addMessage(new Message('Autor o podanym imieniu i nazwisku istnieje w bazie!', Message::ERROR));
                    $walidacja = false;
                }
            }
        }
        return $walidacja;
    }
    
    private function zapiszDaneNaBazeDodawanie()
    {
        try
        {
            App::getDB()->insert("AUTOR", [
                "imie_autora" => strval($this->form->imie),
                "nazwisko_autora" => strval($this->form->nazwisko),
            ]);
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas dodawania autora.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie dodano autora.', Message::INFO));
        }
    }
    
    private function generujWidokWyswietl()
    {
        App::getSmarty()->assign('page_title','zarządzanie autorami');
        App::getSmarty()->assign('form',$this->form);
        App::getSmarty()->assign('result',$this->result);
        
        App::getSmarty()->display('autor.tpl');
    }
    
    private function generujWidokDodaj()
    {
        App::getSmarty()->assign('page_title','zarządzanie autorami');
        App::getSmarty()->assign('form',$this->form);
        
        App::getSmarty()->display('AutorDodaj.tpl');
    }
}
