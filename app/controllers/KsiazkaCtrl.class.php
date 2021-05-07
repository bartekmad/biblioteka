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
    private $result2;
    private $autorzy;
    private $rolaUzytkownika;
    private $listaKategorii;
    private $listaAutorow;
    private $czyZalogowany;
    private $czyEdytuj;
    
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
        $this->czyEdytuj = false;
        $this->listaKategorii = App::getDB()->select("KATEGORIA", "*");
        $this->listaAutorow = App::getDB()->select("AUTOR", "*");
        $this->generujWidokDodajEdytuj();
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
        $this->form->id_autorow = ParamUtils::getFromRequest('id_autorow',true,'Błędne wywołanie aplikacji');
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
            foreach ($this->form->id_autorow as $dana) 
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
    
    public function action_wyswietlEdytujKsiazke()
    {
        $this->czyEdytuj = true;
        $this->result = App::getDB()->select("KSIAZKA",[
            "id_ksiazki",
            "tytul",
            "id_kategorii",
            "dostepnosc"
            ],
            [
            "id_ksiazki"=>$_GET['id_ksiazki']
            ]
        );
        $this->result2 = App::getDB()->select("AUTORZY_KSIAZKI",[
            "id_autora"
            ],
            [
            "id_ksiazki"=>$_GET['id_ksiazki']
            ]
        );
        $this->listaKategorii = App::getDB()->select("KATEGORIA", "*");
        $this->listaAutorow = App::getDB()->select("AUTOR", "*");
        $this->generujWidokDodajEdytuj();
    }
    
    public function action_edytujKsiazke()
    {
        $this->pobierzParametryDodawania();
        if ($this->czyWpisaneWartosciDodawania())
        {
            if ($this->walidujDodawanieKsiazki())
                $this->zapiszDaneNaBazeEdytowanie();
        }
        $this->action_wyswietlEdytujKsiazke();  
    }
    
    private function zapiszDaneNaBazeEdytowanie()
    {
        try
        {
            App::getDB()->delete("AUTORZY_KSIAZKI", [
                "id_ksiazki" => $_GET['id_ksiazki']
            ]);
            
            foreach ($this->form->id_autorow as $dana) 
            {
                App::getDB()->insert("AUTORZY_KSIAZKI", [
                    "id_ksiazki" => $_GET['id_ksiazki'],
                    "id_autora" => intval($dana)
                ]);
            }

            App::getDB()->update("KSIAZKA", [
                "tytul" => strval($this->form->tytul),
                "id_kategorii" => intval($this->form->id_kategorii),
                "dostepnosc" => intval($this->form->dostepnosc),
                ],
                [
                "id_ksiazki" => $_GET['id_ksiazki']
            ]);
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wystąpił błąd podczas edytowania książki.', Message::ERROR));
        }
        finally
        {
            App::getMessages()->addMessage(new Message('Pomyślnie zedytowano książkę.', Message::INFO));
        }
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
    
    private function generujWidokDodajEdytuj()
    {
        App::getSmarty()->assign('page_title','lista książek');
        App::getSmarty()->assign('listaKategorii',$this->listaKategorii);
        App::getSmarty()->assign('listaAutorow',$this->listaAutorow);
        App::getSmarty()->assign('form',$this->form);
        App::getSmarty()->assign('czyEdytuj',$this->czyEdytuj);
        App::getSmarty()->assign('result',$this->result);
        App::getSmarty()->assign('result2',$this->result2);
        
        App::getSmarty()->display('ksiazkaDodaj.tpl');
    }
}
