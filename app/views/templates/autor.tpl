{extends file="main.tpl"}

{block name=content}
    <a class="pure-button" href="{$conf->action_url}wyswietlDodajAutora" class="button special small">Dodaj autora</a>
    <br><br>
{if $result}
    {if (count($result) > 0)}
        <table class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>lp.</th>
                    <th>imię</th>
                    <th>nazwisko</th>
                    <th>operacje</th>
                </tr>
            </thead>
            <tbody>
                {foreach $result as $dana}
                    <tr>
                        <td>{$dana["id_autora"]}</td>
                        <td>{$dana["imie_autora"]}</td>
                        <td>{$dana["nazwisko_autora"]}</td>
                        <td>
                            <a class="pure-button" href="{$conf->action_url}WyswietlEdytujAutora?id_autora={$dana["id_autora"]}" class="button special small">Edytuj</a>
                            <a class="pure-button" href="{$conf->action_url}usunAutora?id_autora={$dana["id_autora"]}" class="button special small">Usun</a>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
{/if}
{/block}