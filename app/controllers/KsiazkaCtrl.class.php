<?php
namespace app\controllers;

use app\forms\KsiazkaForm;
use core\App;
use core\SessionUtils;
use core\Message;
use core\ParamUtils;
use PDOException;

class KsiazkaCtrl
{
    private $form;
    private $result;
    private $autorzy;
    private $rolaUzytkownika;
    private $listaKategorii;
    private $listaAutorow;
    private $wybranaKategoria;
    private $wybranyAutor;
    private $czyZalogowany;
    
    public function __construct()
    {
        $this->form = new KsiazkaForm();
    }
    
    public function action_przegladanieKsiazek()
    {
        $this->rolaUzytkownika = intval(SessionUtils::load("user",true)['id_uprawnienia']);
        
        $this->result = App::getDB()->select("KSIAZKA", [
                "[>]KATEGORIA" => ["id_kategorii" => "id_kategorii"]
            ],
            [    
                "KSIAZKA.tytul",
                "KSIAZKA.dostepnosc",
                "KSIAZKA.id_ksiazki",
                "KATEGORIA.nazwa_kategori"
            ]);
        
        $this->autorzy = App::getDB()->select("AUTOR", [
                "[>]AUTORZY_KSIAZKI" => ["id_autora" => "id_autora"]
            ],
            [
                "AUTORZY_KSIAZKI.id_ksiazki",
                "AUTOR.imie_autora",
                "AUTOR.nazwisko_autora"
        ]);
        
        $this->generujWidokWyswietl();
    }
    
    public function action_wyswietlDodajKsiazke()
    {
        $this->listaKategorii = App::getDB()->select("KATEGORIA", "*");
        $this->listaAutorow = App::getDB()->select("AUTOR", "*");
        $this->generujWidokDodaj();
    }
    
    public function action_dodajKsiazke()
    {
        $this->pobierzParametryDodawania();
        if ($this->czyWpisaneWartosciDodawania())
        {
            if ($this->walidujDodawanieKsiazki())
                $this->zapiszDaneNaBazeDodawanie();
        }
        $this->action_wyswietlDodajKsiazke();   
    }
    
    private function pobierzParametryDodawania()
    {
        $this->form->tytul = ParamUtils::getFromRequest('tytul',true,'Błędne wywołanie aplikacji');
        $this->form->dostepnosc = ParamUtils::getFromRequest('dostepnosc',true,'Błędne wywołanie aplikacji');
        $this->form->id_kategorii = ParamUtils::getFromRequest('id_kategorii',true,'Błędne wywołanie aplikacji');
        $this->form->id_autorow = ParamUtils::getFromRequest('id_kategorii',true,'Błędne wywołanie aplikacji');
    }
    
    private function czyWpisaneWartosciDodawania()
    {
        return (isset($this->form->tytul) && isset($this->form->dostepnosc) && isset($this->form->id_kategorii) && isset($this->form->id_autorow));
    }
    
        private function walidujDodawanieKsiazki()
    {
        $walidacja = true;

        if ($this->form->tytul == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano tytułu książki!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->dostepnosc == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano dostępności książki!', Message::ERROR));
            $walidacja = false;
        }
        
        $wynik = App::getDB()->select("KSIAZKA",[
            "tytul",
            ],
            [
            "tytul"=>$this->form->tytul
            ]
        );
        if (count($wynik) > 0)
        {
            foreach($wynik as $dana)
            {
                if ($this->form->tytul == $dana["tytul"])
                {
                    App::getMessages()->addMessage(new Message('Książka o podanym tytule istnieje w bazie!', Message::ERROR));
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
            App::getDB()->insert("KSIAZKA", [
                "tytul" => strval($this->form->tytul),
                "id_kategorii" => intval($this->form->id_kategorii),
                "dostepnosc" => intval($this->form->dostepnosc),
            ]);
            $idKsiazki = App::getDB()->select("KSIAZKA",[
                "id_ksiazki",
                ],
                [
                "tytul"=>$this->form->tytul
                ]
            );
            foreach (str_split($this->form->id_autorow)as $dana) 
            {
                App::getDB()->insert("AUTORZY_KSIAZKI", [
                    "id_ksiazki" => intval($idKsiazki[0]['id_ksiazki']),
                    "id_autora" => intval($dana)
                ]);
            }
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas dodawania książki.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie dodano książkę.', Message::INFO));
        }
    }
    
    public function action_edytujKsiazke()
    {
        
    }
    
    public function action_usunKsiazke()
    {
        try
        {
            App::getDB()->delete("KSIAZKA", [
                "id_ksiazki" => $_GET['id_ksiazki']
            ]);
            App::getDB()->delete("AUTORZY_KSIAZKI", [
                "id_ksiazki" => $_GET['id_ksiazki']
            ]);
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas usuwania książki.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie usunięto książkę.', Message::INFO));
        }
        $this->action_przegladanieKsiazek();  
    }
    
    public function action_ZarezerwujKsiazke()
    {
        try
        {
            $idZalogowanegoUzytkownika = intval(SessionUtils::load("user",true)['id_uzytkownika']);
            App::getDB()->insert("REZERWACJA", [
                "id_ksiazki" => $_GET['id_ksiazki'],
                "data_rezerwacji" => date('Y-m-d'),
                "id_uzytkownika" => intval($idZalogowanegoUzytkownika)
            ]);
            
            $dostepnoscKsiazki= App::getDB()->select("KSIAZKA",[
                "dostepnosc",
                ],
                [
                "id_ksiazki" => $_GET['id_ksiazki']
                ]
            );
            
            App::getDB()->update("KSIAZKA", [
                "dostepnosc" => intval($dostepnoscKsiazki[0]['dostepnosc'])-1
                ],
                [
                "id_ksiazki" => $_GET['id_ksiazki']
            ]);
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas dokonywania rezerwacji.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie zarezerwowano książkę.', Message::INFO));
        }
        $this->action_przegladanieKsiazek();  
    }
    
    private function generujWidokWyswietl()
    {
        App::getSmarty()->assign('page_title','lista książek');
        App::getSmarty()->assign('result',$this->result);
        App::getSmarty()->assign('autorzy',$this->autorzy);
        App::getSmarty()->assign('rolaUzytkownika',$this->rolaUzytkownika);
        $this->czyZalogowany = 0 != intval(SessionUtils::load("user",true)['id_uzytkownika']);
        App::getSmarty()->assign('czyZalogowany',$this->czyZalogowany);
                
        App::getSmarty()->display('ksiazka.tpl');
    }
    
    private function generujWidokDodaj()
    {
        App::getSmarty()->assign('page_title','lista książek');
        App::getSmarty()->assign('listaKategorii',$this->listaKategorii);
        App::getSmarty()->assign('listaAutorow',$this->listaAutorow);
        App::getSmarty()->assign('wybranaKategoria',$this->wybranaKategoria);
        App::getSmarty()->assign('wybranyAutor',$this->wybranyAutor);
        App::getSmarty()->assign('form',$this->form);
        
        App::getSmarty()->display('ksiazkaDodaj.tpl');
    }
}
