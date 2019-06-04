<?php
echo '<table>';
foreach ($_SERVER as $key => $value) {
    $fkey='_'.$key;
    #        if ( strpos($fkey,'SHIB')>1 && $key!="HTTP_SHIB_ATTRIBUTES")
    #       if ( strpos($fkey,'SHIB')>1 )
    #        {
    echo '<tr>';
    echo '<td>'.$key.'</td><td>'.$value.'</td>';
    echo '</tr>';
    #        }
}
echo '<tr><td>(REMOTE_USER)</td><td>'.$_SERVER['REMOTE_USER'].'</td></tr>';
echo '<tr><td>(HTTP_REMOTE_USER)</td><td>'.$_SERVER['HTTP_REMOTE_USER'].'</td></tr>';
echo '<tr><td>DECONNEXION</td><td>'.'<a href="/Shibboleth.sso/Logout?return='.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/secure">[logout]</a> </td></tr>';

echo '</table>';
