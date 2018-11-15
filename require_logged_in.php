<?php
if(!is_user_logged_in()) {
    $url = get_bloginfo('url') . '/'. EXAM_LOGIN_SLUG;
    
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';
    echo $string;
    exit();
}