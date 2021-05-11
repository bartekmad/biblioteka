-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Czas generowania: 07 Maj 2021, 15:03
-- Wersja serwera: 5.7.26
-- Wersja PHP: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `biblioteka`
--
CREATE DATABASE IF NOT EXISTS `id16710280_biblioteka` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `id16710280_biblioteka`;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `autor`
--

DROP TABLE IF EXISTS `autor`;
CREATE TABLE IF NOT EXISTS `autor` (
  `id_autora` int(11) NOT NULL AUTO_INCREMENT,
  `imie_autora` varchar(45) NOT NULL,
  `nazwisko_autora` varchar(45) NOT NULL,
  PRIMARY KEY (`id_autora`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `autorzy_ksiazki`
--

DROP TABLE IF EXISTS `autorzy_ksiazki`;
CREATE TABLE IF NOT EXISTS `autorzy_ksiazki` (
  `id_autorow_ksiazki` int(11) NOT NULL AUTO_INCREMENT,
  `id_ksiazki` int(11) NOT NULL,
  `id_autora` int(11) NOT NULL,
  PRIMARY KEY (`id_autorow_ksiazki`),
  KEY `fkIdx_92` (`id_ksiazki`),
  KEY `fkIdx_95` (`id_autora`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kategoria`
--

DROP TABLE IF EXISTS `kategoria`;
CREATE TABLE IF NOT EXISTS `kategoria` (
  `id_kategorii` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_kategori` varchar(45) NOT NULL,
  PRIMARY KEY (`id_kategorii`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ksiazka`
--

DROP TABLE IF EXISTS `ksiazka`;
CREATE TABLE IF NOT EXISTS `ksiazka` (
  `id_ksiazki` int(11) NOT NULL AUTO_INCREMENT,
  `tytul` varchar(100) NOT NULL,
  `dostepnosc` int(11) NOT NULL,
  `id_kategorii` int(11) NOT NULL,
  PRIMARY KEY (`id_ksiazki`),
  KEY `fkIdx_78` (`id_kategorii`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacja`
--

DROP TABLE IF EXISTS `rezerwacja`;
CREATE TABLE IF NOT EXISTS `rezerwacja` (
  `id_rezerwacji` int(11) NOT NULL AUTO_INCREMENT,
  `id_ksiazki` int(11) NOT NULL,
  `data_rezerwacji` date DEFAULT NULL,
  `data_wypozyczenia` date DEFAULT NULL,
  `data_zwrotu` date NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  PRIMARY KEY (`id_rezerwacji`),
  KEY `fkIdx_41` (`id_ksiazki`),
  KEY `fkIdx_53` (`id_uzytkownika`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uprawnienia`
--

DROP TABLE IF EXISTS `uprawnienia`;
CREATE TABLE IF NOT EXISTS `uprawnienia` (
  `id_uprawnienia` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa_uprawnienia` varchar(45) NOT NULL,
  PRIMARY KEY (`id_uprawnienia`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownik`
--

DROP TABLE IF EXISTS `uzytkownik`;
CREATE TABLE IF NOT EXISTS `uzytkownik` (
  `id_uzytkownika` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(45) NOT NULL,
  `haslo` varchar(45) NOT NULL,
  `imie` varchar(45) NOT NULL,
  `nazwisko` varchar(45) NOT NULL,
  `id_uprawnienia` int(11) NOT NULL,
  PRIMARY KEY (`id_uzytkownika`),
  KEY `fkIdx_85` (`id_uprawnienia`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
