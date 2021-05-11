{extends file="main.tpl"}

{block name=content}
{if $result}
    {if (count($result) > 0)}
        <table class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>lp.</th>
                    {if $rolaUzytkownika < 2}<th>użytkownik</th>{/if}
                    <th>książka</th>
                    <th>data rezerwacji</th>
                    <th>data wypożyczenia</th>
                    <th>data zwrotu</th>
                    <th>operacje</th>
                </tr>
            </thead>
            <tbody>
                {foreach $result as $dana}
                    <tr>
                        <td>{$dana["id_rezerwacji"]}</td>
                        {if $rolaUzytkownika < 2}<td>{$dana["login"]}</td>{/if}
                        <td>{$dana["tytul"]}</td>
                        <td>{$dana["data_rezerwacji"]}</td>
                        <td>{$dana["data_wypozyczenia"]}</td>
                        <td>{$dana["data_zwrotu"]}</td>
                        <td>
                            {if !{$dana["data_wypozyczenia"]} || !{$dana["data_zwrotu"]}}
                                <a class="pure-button" href="{$conf->action_url}anulujRezerwacje?id_rezerwacji={$dana["id_rezerwacji"]}" class="button special small">Anuluj</a>
                            {/if}
                            {if $rolaUzytkownika < 2}
                                {if !{$dana["data_wypozyczenia"]} && !{$dana["data_zwrotu"]}}
                                    <a class="pure-button" href="{$conf->action_url}dokonajWypozyczenia?id_rezerwacji={$dana["id_rezerwacji"]}" class="button special small">Wypożycz</a>
                                {/if}
                                {if {$dana["data_wypozyczenia"]} && !{$dana["data_zwrotu"]}}
                                    <a class="pure-button" href="{$conf->action_url}zarejestrujZwrot?id_rezerwacji={$dana["id_rezerwacji"]}" class="button special small">Zwróć</a>
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