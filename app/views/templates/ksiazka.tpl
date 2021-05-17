{extends file="main.tpl"}

{block name=content}
{if $rolaUzytkownika < 2 && $czyZalogowany == true}
    <a class="pure-button" href="{$conf->action_url}wyswietlDodajKsiazke" class="button special small">Dodaj książkę</a>
    <br><br>
{/if}

{if $result}
    <form class="pure-form pure-form-stacked" action="{$conf->action_url}przegladanieKsiazek" method="post">
        <fieldset>
            <label for="szukajka">Tytuł: </label>
            <input id="szukajka" type="text" name="szukajka" value="{$form->szukajka}">
        </fieldset>
        <button type="submit" class="pure-button">Szukaj książki</button>
    </form>
        <br><br>
    {if (count($result) > 0)}
        <table class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>id książki</th>
                    <th>tytuł</th>
                    <th>autorzy</th>
                    <th>kategoria</th>
                    <th>dostepnosc</th>
                    {if $czyZalogowany == true}
                        <th>operacje</th>
                    {/if}
                </tr>
            </thead>
            <tbody>
                {foreach $result as $dana}
                    <tr>
                        <td>{$dana["id_ksiazki"]}</td>
                        <td>{$dana["tytul"]}</td>
                        <td>
                            {foreach $autorzy as $autor}
                                {if $autor["id_ksiazki"] == {$dana["id_ksiazki"]}}
                                    {$autor["imie_autora"]}
                                    {$autor["nazwisko_autora"]}<br>
                                {/if}
                            {/foreach}
                        </td>
                        <td>{$dana["nazwa_kategori"]}</td>
                        <td>{$dana["dostepnosc"]}</td>
                        {if $czyZalogowany == true}
                                <td>
                            {if $rolaUzytkownika < 2}
                                    <a class="pure-button" href="{$conf->action_url}wyswietlEdytujKsiazke?id_ksiazki={$dana["id_ksiazki"]}" class="button special small">Edytuj</a>
                                    <a class="pure-button" href="{$conf->action_url}usunKsiazke?id_ksiazki={$dana["id_ksiazki"]}" class="button special small">Usun</a>
                            {/if}
                            {if $dana["dostepnosc"] > 0}
                                    <a class="pure-button" href="{$conf->action_url}ZarezerwujKsiazke?id_ksiazki={$dana["id_ksiazki"]}" class="button special small">Zarezerwuj</a>
                                </td>
                            {/if}
                        {/if}
                        
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
{/if}
{/block}