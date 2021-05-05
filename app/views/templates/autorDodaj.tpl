{extends file="main.tpl"}

{block name=content}
    <form class="pure-form pure-form-stacked" action="{$conf->action_url}dodajAutora" method="post">
        <fieldset>
            <label for="imie">Imię: </label>
            <input id="imie" type="text" name="imie" value="{$form->imie}">
            <label for="nazwisko">Nazwisko: </label>
            <input id="nazwisko" type="text" name="nazwisko" value="{$form->nazwisko}">
        </fieldset>
        <button type="submit" class="pure-button">Dodaj autora</button>
    </form>
{/block}