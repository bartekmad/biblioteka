<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{$page_description|default:'Opis domyślny'}">
    <title>Biblioteka - {$page_title|default:"Tytuł domyślny"}</title>
    <link rel="stylesheet" href="https://unpkg.com/purecss@0.6.2/build/pure-min.css" integrity="sha384-UQiGfs9ICog+LwheBSRCt1o5cbyKIHbwjWscjemyBMT9YCUMZffs6UqUTd0hObXD" crossorigin="anonymous">
    <link rel="stylesheet" href="{$conf->app_url}/css/style.css">	
    <script type="text/javascript" src="{$conf->app_url}/js/functions.js"></script>
</head>
<body>

<div class="header">
    <h1>Biblioteka</h1>
    <h2>{$page_title|default:"Tytuł domyślny"}</h2>
</div>

<div class="menu">
    <div class="pure-menu pure-menu-horizontal">
        <ul class="pure-menu-list">        
{if count($conf->roles)>0}
             {if isset($conf->roles['0']) || isset($conf->roles['1'])}
                <li class="pure-menu-item">
                    <a href="{$conf->action_root}panelUzytkownikow" class="pure-menu-link">Zarządzaj użytkownikami</a>
                </li>
                <li class="pure-menu-item">
                    <a href="{$conf->action_root}panelAutorow" class="pure-menu-link">Zarządzaj autorami</a>
                </li>
            {/if}
{else}
            <li class="pure-menu-item">
                <a href="{$conf->action_root}login" class="pure-menu-link">Zaloguj</a>
            </li>
            <li class="pure-menu-item">
                <a href="{$conf->action_root}panelUzytkownikow" class="pure-menu-link">Zarejestruj się</a>
            </li>
            <li class="pure-menu-item">
                <a href="{$conf->action_root}przegladanieKsiazek" class="pure-menu-link">Przeglądaj książki</a>
            </li>
{/if}
{if count($conf->roles)>0}
            <li class="pure-menu-item">
                <a href="{$conf->action_root}przegladanieKsiazek" class="pure-menu-link">Przeglądaj książki</a>
            </li>
            <li class="pure-menu-item">
                <a href="{$conf->action_root}logout" class="pure-menu-link">Wyloguj</a>
            </li>
{/if}
        </ul>
    </div>
</div>

{block name=messages}
    {if $msgs->isError()}
    <div class="wcinka-msg">
        <div class="pure-u-1-4">
            <div class="err">
                <ul>
                    {foreach $msgs->getMessages() as $msg}
                    {strip}
                        <li>{$msg->text}</li>
                    {/strip}
                    {/foreach}
                </ul>
                <br>
            </div>
        </div>
    </div>
    {/if}

    {if $msgs->isInfo()}
    <div class="wcinka-msg">
        <div class="pure-u-1-4">
            <div class="inf">
                <ul>
                    {foreach $msgs->getMessages() as $msg}
                    {strip}
                        <li>{$msg->text}</li>
                    {/strip}
                    {/foreach}
                </ul>
                <br>
            </div>
        </div>
    </div>
    {/if}
{/block}

<div class="content">
{block name=content}
{/block}
</div>

</body>
</html>