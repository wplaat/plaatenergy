<?php

/* 
**  ===========
**  PlaatEnergy
**  ===========
**
**  Created by wplaat
**
**  For more information visit the following website.
**  Website : www.plaatsoft.nl 
**
**  Or send an email to the following address.
**  Email   : info@plaatsoft.nl
**
**  All copyrights reserved (c) 2008-2016 PlaatSoft
*/

/**
 * @file
 * @brief contain about page
 */

/*
** ---------------------
** PAGE
** ---------------------
*/

/**
 * plaatenergy about page
 * @return HTML block which page contain.
 */
function plaatenergy_about_page() {

  $page  = '<h1>'.t('ABOUT_TITLE').'</h1>';
  $page .= '<br/>';
  $page .= '<div class="large_text">'.t('ABOUT_CONTENT').'</div>';

  $page .= '<br/>';
  $page .= '<img class="image" src="images/logo.jpg" alt="" width="80" height="60">';

  $page .= '<br/>';
  $page .= '<h2>'.t('CREDITS_TITLE').'</h2>';
  $page .= '<div class="large_text">'.t('CREDITS_CONTENT').'</div>';

  $page .= '<br/>';
  $page .= '<h2>'.t('DISCLAIMER_TITLE').'</h2>';
  $page .= '<div class="large_text">'.t('DISCLAIMER_CONTENT').'</div>';

  $page .= '<div class="nav">';
  $page .= plaatenergy_link('pid='.PAGE_HOME, t('LINK_HOME'));
  $page .=  '</div>';

  return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

/**
 * plaatenergy about handler
 * @return HTML block which page contain.
 */
function plaatenergy_about() {

  /* input */
  global $pid;

  /* Page handler */
  switch ($pid) {

     case PAGE_ABOUT:
        return plaatenergy_about_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
