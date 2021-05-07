<?php

use core\App;
use core\Utils;

App::getRouter()->setDefaultRoute('przegladanieKsiazek');
App::getRouter()->setLoginRoute('login');
Utils::addRoute('login', 'LoginCtrl');
Utils::addRoute('logout', 'LoginCtrl');

Utils::addRoute('przegladanieKsiazek', 'KsiazkaCtrl');
Utils::addRoute('wyswietlDodajKsiazke', 'KsiazkaCtrl', ['0','1']);
Utils::addRoute('wyswietlEdytujKsiazke', 'KsiazkaCtrl', ['0','1']);
Utils::addRoute('dodajKsiazke', 'KsiazkaCtrl', ['0','1']);
Utils::addRoute('edytujKsiazke', 'KsiazkaCtrl', ['0','1']);
Utils::addRoute('usunKsiazke', 'KsiazkaCtrl', ['0','1']);
Utils::addRoute('ZarezerwujKsiazke', 'KsiazkaCtrl', ['0','1','2']);

Utils::addRoute('zarzadzajUzytkownikami', 'UzytkownicyCtrl');
Utils::addRoute('dodajUzytkownika', 'UzytkownicyCtrl');
Utils::addRoute('edytujUzytkownika', 'UzytkownicyCtrl', ['0','1']);
Utils::addRoute('panelUzytkownikow', 'UzytkownicyCtrl');

Utils::addRoute('panelAutorow', 'AutorCtrl', ['0','1']);
Utils::addRoute('wyswietlDodajAutora', 'AutorCtrl', ['0','1']);
Utils::addRoute('wyswietlEdytujAutora', 'AutorCtrl', ['0','1']);
Utils::addRoute('dodajAutora', 'AutorCtrl', ['0','1']);
Utils::addRoute('edytujAutora', 'AutorCtrl', ['0','1']);
Utils::addRoute('usunAutora', 'AutorCtrl', ['0','1']);