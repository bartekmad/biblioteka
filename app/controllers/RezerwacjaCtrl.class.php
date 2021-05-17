<?php
namespace app\controllers;

use core\App;
use core\SessionUtils;
use core\Message;
use core\ParamUtils;
use PDOException;

class RezerwacjaCtrl 
{
    private $result;
    private $rolaUzytkownika;
    
    public function __construct()
    {
    }
    
    public function action_przegladanieRezerwacji()
    {
        $this->rolaUzytkownika = intval(SessionUtils::load("user",true)['id_uprawnienia']);
        
        if ($this->rolaUzytkownika == 2)
        {
            $idUsera = intval(SessionUtils::load("user",true)['id_uzytkownika']);
            $this->result = App::getDB()->select("REZERWACJA", [
                    "[>]UZYTKOWNIK" => ["id_uzytkownika" => "id_uzytkownika"],
                    "[>]KSIAZKA" => ["id_ksiazki" => "id_ksiazki"]
                ],
                [   
                    "REZERWACJA.id_rezerwacji",
                    "UZYTKOWNIK.login",
                    "KSIAZKA.tytul",
                    "REZERWACJA.data_rezerwacji",
                    "REZERWACJA.data_wypozyczenia",
                    "REZERWACJA.data_zwrotu"
                ],
                ["UZYTKOWNIK.id_uzytkownika"=>$idUsera,
                "ORDER" => ["id_rezerwacji" => "DESC"]]);    
        }
        else
        {
            $this->result = App::getDB()->select("REZERWACJA", [
                    "[>]UZYTKOWNIK" => ["id_uzytkownika" => "id_uzytkownika"],
                    "[>]KSIAZKA" => ["id_ksiazki" => "id_ksiazki"]
                ],
                [   
                    "REZERWACJA.id_rezerwacji",
                    "UZYTKOWNIK.login",
                    "KSIAZKA.tytul",
                    "REZERWACJA.data_rezerwacji",
                    "REZERWACJA.data_wypozyczenia",
                    "REZERWACJA.data_zwrotu"
                ],
                ["ORDER" => ["id_rezerwacji" => "DESC"]]);
        }
        
        $this->generujWidokWyswietl();
    }
    
    public function action_anulujRezerwacje()
    {
        try
        {
            $idKsiazki = App::getDB()->select("REZERWACJA",[
                "id_ksiazki"
                ],
                [
                "id_rezerwacji"=>$_GET['id_rezerwacji']
            ]);
            
            App::getDB()->delete("REZERWACJA", [
                "id_rezerwacji" => $_GET['id_rezerwacji']
            ]);
            
            $dostepnoscKsiazki= App::getDB()->select("KSIAZKA",[
                "dostepnosc"
                ],
                [
                "id_ksiazki" => $idKsiazki[0]['id_ksiazki']
                ]
            );
            
            App::getDB()->update("KSIAZKA", [
                "dostepnosc" => intval($dostepnoscKsiazki[0]['dostepnosc'])+1
                ],
                [
                "id_ksiazki" => $idKsiazki[0]['id_ksiazki']
            ]);
            App::getMessages()->addMessage(new Message('Pomyślnie usunięto rezerwację.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas usuwania rezerwacji.', Message::ERROR));
        }
        finally
        {
            $this->action_przegladanieRezerwacji(); 
        }
    }
    
    public function action_dokonajWypozyczenia()
    {
        try
        {        
            App::getDB()->update("REZERWACJA", [
                "data_wypozyczenia" => date('Y-m-d')
                ],
                [
                "id_rezerwacji"=>$_GET['id_rezerwacji']
            ]);
            App::getMessages()->addMessage(new Message('Pomyślnie wypożyczono książkę.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas wypożyczania.', Message::ERROR));
        }
        finally
        {
            $this->action_przegladanieRezerwacji(); 
        }
    }
    
    public function action_zarejestrujZwrot()
    {
        try
        {        
            App::getDB()->update("REZERWACJA", [
                "data_zwrotu" => date('Y-m-d')
                ],
                [
                "id_rezerwacji"=>$_GET['id_rezerwacji']
            ]);
            App::getMessages()->addMessage(new Message('Pomyślnie zwrócono książkę.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas zwrotu.', Message::ERROR));
        }
        finally
        {
            $this->action_przegladanieRezerwacji(); 
        }
    }
    
    private function generujWidokWyswietl()
    {
        App::getSmarty()->assign('page_title','lista rezerwacji');
        App::getSmarty()->assign('result',$this->result);
        App::getSmarty()->assign('rolaUzytkownika',$this->rolaUzytkownika);
                
        App::getSmarty()->display('rezerwacja.tpl');
    }
}
