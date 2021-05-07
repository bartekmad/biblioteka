{extends file="main.tpl"}

{block name=content}
    <form class="pure-form pure-form-stacked" action="{$conf->action_url}{if $czyEdytuj == true}edytujKsiazke?id_ksiazki={$result[0]['id_ksiazki']}{else}dodajKsiazke{/if}" method="post">
        <fieldset>
            <label for="tytul">Tytuł książki: </label>
            <input id="tytul" type="text" name="tytul" value="{if $result}{$result[0]['tytul']}{/if}{$form->tytul}">
            <label for="dostepnosc">Dostepnosc: </label>
            <input id="dostepnosc" type="number" name="dostepnosc" value="{if $result}{$result[0]['dostepnosc']}{/if}{$form->dostepnosc}">
            <label for="id_kategorii">Kategoria: </label>
                <select list="id_kategorii" id="id_kategorii" name="id_kategorii" value="{$form->id_kategorii}">
                    <datalist id="id_kategorii">
                        {if $listaKategorii}
                            {foreach $listaKategorii as $dana}
                                <option value="{$dana["id_kategorii"]}"{if $dana["id_kategorii"]=={$result[0]['id_kategorii']}}selected{/if}>{$dana["nazwa_kategori"]}</option>
                            {/foreach}
                        {/if}
                    </datalist>
                </select>
            <label for="id_autorow">Autorzy: </label>
                <select list="id_autorow" id="id_autorow" multiple="multiple" name="id_autorow[]" value="{$form->id_autorow}">
                    <datalist id="id_autorow">
                        {if $listaAutorow}
                            
                            {foreach $listaAutorow as $autor}
                                
                                <option value="{$autor["id_autora"]}"{foreach $result2 as $autorzyna}{if $autor["id_autora"]=={$autorzyna['id_autora']}}selected{/if}{/foreach}>{$autor["imie_autora"]} {$autor["nazwisko_autora"]}</option>
                            
                                
                            {/foreach}
                        {/if}
                    </datalist>
                </select>
        </fieldset>
        <button type="submit" class="pure-button">{if $czyEdytuj == true}Edytuj książkę{else}Dodaj książkę{/if}</button>
    </form>
{/block}