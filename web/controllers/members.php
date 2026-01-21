<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

$meta_title = '纽约同城婚介交友 - 会员列表';
$meta_description = '纽约同城婚介交友的会员列表，在这儿，希望你能找到一个你喜欢的男朋友或女朋友，这儿的人大多数都在纽约，特别是在法拉盛。 祝你好运。';
$meta_keywords = '纽约婚介交友, 法拉盛婚介找友, 纽约找男朋友';

$members_factory = new Members_Factory();
$members = $members_factory->get_all_active_members();


//Render page.
require_once( ROOT_PATH . '/views/page-members.php' );