{extends file="main.tpl"}

{block name=content}
{if $rolaUzytkownika < 2 && $czyZalogowany == true}
    <a class="pure-button" href="{$conf->action_url}wyswietlDodajKsiazke" class="button special small">Dodaj książkę</a>
    <br><br>
{/if}
{if $result}
    {if (count($result) > 0)}
        <table class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>lp.</th>
                    <th>tytuł</th>
                    <th>autorzy</th>
                    <th>kategoria</th>
                    <th>dostepnosc</th>
                    <th>operacje</th>
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
                        <td>
                            {if $czyZalogowany == true}
                                {if $rolaUzytkownika < 2}
                                    <a class="pure-button" href="{$conf->action_url}WyswietlEdytujKsiazke?id_ksiazki={$dana["id_ksiazki"]}" class="button special small">Edytuj</a>
                                    <a class="pure-button" href="{$conf->action_url}usunKsiazke?id_ksiazki={$dana["id_ksiazki"]}" class="button special small">Usun</a>
                                {/if}
                                {if $dana["dostepnosc"] > 0}
                                    <a class="pure-button" href="{$conf->action_url}ZarezerwujKsiazke?id_ksiazki={$dana["id_ksiazki"]}" class="button special small">Zarezerwuj</a>
                                {/if}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
{/if}
{/block}