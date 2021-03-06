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
        $this->form->szukajka = ParamUtils::getFromRequest('szukajka');
        
        $search_params = [];
	if (isset($this->form->szukajka)) 
            $search_params['tytul[~]'] = $this->form->szukajka.'%';                
                        
	$num_params = sizeof($search_params); 
	if (intval($num_params) >= 1) {
            $where = [ "AND" => &$search_params ];
	} else {
            $where = ["ORDER" => "tytul"];
	}
        
        $this->result = App::getDB()->select("KSIAZKA", [
                "[>]KATEGORIA" => ["id_kategorii" => "id_kategorii"]
            ],
            [    
                "KSIAZKA.tytul",
                "KSIAZKA.dostepnosc",
                "KSIAZKA.id_ksiazki",
                "KATEGORIA.nazwa_kategori"
            ],
            $where);
        
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
        $this->form->tytul = ParamUtils::getFromRequest('tytul',true,'B????dne wywo??anie aplikacji');
        $this->form->dostepnosc = ParamUtils::getFromRequest('dostepnosc',true,'B????dne wywo??anie aplikacji');
        $this->form->id_kategorii = ParamUtils::getFromRequest('id_kategorii',true,'B????dne wywo??anie aplikacji');
        $this->form->id_autorow = ParamUtils::getFromRequest('id_autorow',true,'B????dne wywo??anie aplikacji');
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
            App::getMessages()->addMessage(new Message('Nie podano tytu??u ksi????ki!', Message::ERROR));
            $walidacja = false;
        }
        if ($this->form->dostepnosc == "")
        {
            App::getMessages()->addMessage(new Message('Nie podano dost??pno??ci ksi????ki!', Message::ERROR));
            $walidacja = false;
        }
        
        if ($this->form->dostepnosc < 0)
        {
            App::getMessages()->addMessage(new Message('Dost??pno???? nie mo??e by?? warto??ci?? ujemn??!', Message::ERROR));
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
                    App::getMessages()->addMessage(new Message('Ksi????ka o podanym tytule istnieje w bazie!', Message::ERROR));
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
            App::getMessages()->addMessage(new Message('Pomy??lnie dodano ksi????k??.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wyst??pi?? b????d podczas dodawania ksi????ki.', Message::ERROR));
        }
        finally
        {
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
            App::getMessages()->addMessage(new Message('Pomy??lnie zedytowano ksi????k??.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wyst??pi?? b????d podczas edytowania ksi????ki.', Message::ERROR));
        }
        finally
        {
        }
    }
    
    public function action_usunKsiazke()
    {
        try
        {
            $rezerwacjeKsiazki = App::getDB()->select("REZERWACJA",[
                "id_ksiazki"
                ],
                [
                "id_ksiazki" => $_GET['id_ksiazki']
                ]
            );
            
            if (count($rezerwacjeKsiazki) > 0)
            {
                App::getMessages()->addMessage(new Message('Nie mo??na usun???? ksi????ki, kt??ra by??a zarezerwowana.', Message::ERROR));
                throw new PDOException();
            }
            else
            {
                App::getDB()->delete("KSIAZKA", [
                    "id_ksiazki" => $_GET['id_ksiazki']
                ]);
                App::getDB()->delete("AUTORZY_KSIAZKI", [
                    "id_ksiazki" => $_GET['id_ksiazki']
                ]);
            }
            App::getMessages()->addMessage(new Message('Pomy??lnie usuni??to ksi????k??.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wyst??pi?? b????d podczas usuwania ksi????ki.', Message::ERROR));
        }
        finally
        {
            $this->action_przegladanieKsiazek(); 
        }
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
            App::getMessages()->addMessage(new Message('Pomy??lnie zarezerwowano ksi????k??.', Message::INFO));
        }
        catch (PDOException $e)
        {
            App::getMessages()->addMessage(new Message('Wyst??pi?? b????d podczas dokonywania rezerwacji.', Message::ERROR));
        }
        finally
        {
            $this->action_przegladanieKsiazek();  
        }
    }
    
    private function generujWidokWyswietl()
    {
        App::getSmarty()->assign('page_title','lista ksi????ek');
        App::getSmarty()->assign('form',$this->form);
        App::getSmarty()->assign('result',$this->result);
        App::getSmarty()->assign('autorzy',$this->autorzy);
        App::getSmarty()->assign('rolaUzytkownika',$this->rolaUzytkownika);
        $this->czyZalogowany = 0 != intval(SessionUtils::load("user",true)['id_uzytkownika']);
        App::getSmarty()->assign('czyZalogowany',$this->czyZalogowany);
                
        App::getSmarty()->display('ksiazka.tpl');
    }
    
    private function generujWidokDodajEdytuj()
    {
        App::getSmarty()->assign('page_title','lista ksi????ek');
        App::getSmarty()->assign('listaKategorii',$this->listaKategorii);
        App::getSmarty()->assign('listaAutorow',$this->listaAutorow);
        App::getSmarty()->assign('form',$this->form);
        App::getSmarty()->assign('czyEdytuj',$this->czyEdytuj);
        App::getSmarty()->assign('result',$this->result);
        App::getSmarty()->assign('result2',$this->result2);
        
        App::getSmarty()->display('ksiazkaDodaj.tpl');
    }
}
