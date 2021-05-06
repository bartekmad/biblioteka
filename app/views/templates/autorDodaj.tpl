{extends file="main.tpl"}

{block name=content}
    <form class="pure-form pure-form-stacked" action="{$conf->action_url}{if $czyEdytuj == true}edytujAutora?id_autora={$result[0]['id_autora']}{else}dodajAutora{/if}" method="post">
        <fieldset>
            <label for="imie">Imię: </label>
            <input id="imie" type="text" name="imie" value="{if $result}{$result[0]['imie_autora']}{/if}{$form->imie}">
            <label for="nazwisko">Nazwisko: </label>
            <input id="nazwisko" type="text" name="nazwisko" value="{if $result}{$result[0]['nazwisko_autora']}{/if}{$form->nazwisko}">
        </fieldset>
        <button type="submit" class="pure-button">{if $czyEdytuj == true}Edytuj autora{else}Dodaj autora{/if}</button>
    </form>
{/block}