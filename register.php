
﻿<html>

    <head>
        <meta charset="utf-8">
        <title>SkyForce Aero Test</title>
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <!--<script type="text/javascript" src="js/jquery-ui.js"></script>-->
        <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.form.min.js"></script>
        <script type="text/javascript" src="js/register-mobile.js?version=1m"></script>
        <script type="text/javascript" src="js/json2xml.js"></script>
        <script src="js/jquery.inputmask.bundle.js"></script>

        <style>
            #dialog .arrowCase {
                height: auto !important;
                padding: 0;
                position: relative;
            }
            .arrowCase button {
                width: 23px;
                position: absolute;
                padding: 0;
                right: 2px;
                height: 18px;
                border: none;
                outline: none;
                background: #c8c8c8;
                border-radius: 5px;
            }
            .arrowCase + input {
                padding-right: 35px !important;
            }
            .minus {
                top: 27px;
            }
            .plus {
                top: 5px;
            }
            .plus::after, .minus::after {
                cursor: pointer;
                content: ''; 
                position: absolute;
                right: -5px; 
                top: 3px;
                background: url('img/arrows.svg') no-repeat;
                width: 100%;
                height: 100%;
            }
            
            .minus::after {
                transform: rotate(180deg);
                top: -3px;
                right: 5px;
            }
            .plus:hover, .minus:hover{
                background: #a5a2a2;
                transition: 0.8s;
            }
            input.removeArrow::-webkit-outer-spin-button,
            input.removeArrow::-webkit-inner-spin-button {
                -webkit-appearance: none;
            }
            input.removeArrow {
                -moz-appearance: textfield;
            }
            body {
                display: grid;
                grid-template-areas:
                    "header header header"
                    "nav article article";
                /*footer footer footer";*/
                grid-template-rows: /*60px 1fr 60px;*/ 56px 1fr;
                grid-template-columns: 363px 1fr;/* 15%;8/
                grid-gap: 0px; /*10px;*/
                height: 100vh;
                margin: 0;
            }
            header{
                display: grid;
                grid-template-areas:
                    "logo reg-llc reg-pe reg-sp";
                grid-template-columns: minmax(363px, 1fr) repeat(3, minmax(150px, .5fr));
                background: white;
                box-shadow: 2px 0px 8px rgba(245,247,247,0.12);

            }

            footer, article, div {
                padding: 20px;
                background: white;
            }


            nav {
                padding: 20px;
                background: rgb(245, 247, 247);
            }

            #pageHeader {
                grid-area: header;
                z-index: 1000;
                position: fixed;
                width: 100%;
                border-bottom: 1px solid lightgray;
            }



            #pageFooter {
                grid-area: footer;
            }
            #mainArticle {
                grid-area: article;
                grid-gap: 20px;
                display: grid;
                grid-template-areas:
                    "commands" "dialog";
                grid-template-rows: 40px 1fr;
            }

            #dialog {
                grid-area: dialog;
            }

            #commands {
                grid-area: commands;

                display: grid;
                grid-template-areas:
                    "move-prev download move-next";
                grid-template-columns: /*60px 1fr 60px;*/ 100px 1fr 100px;

                padding: 0px;
                background: white;
            }

            #mainNav {
                padding:0;
                grid-area: nav;

            }
            #siteAds {
                grid-area: ads;
            }

            #navWrapper {
                padding: 60px 0px 24px 91px;
                background: rgb(245, 247, 247);
            }

            #navTitle {
                position: relative;
                float:left;
                height: 36px;
                width:100%;
                padding:0;
                background: rgb(245, 247, 247);
                line-height: 36px;
                font-size:20px;
                font-family: Arial;
                font-weight:700;
            }

            #navList {
                position: relative;
                float:left;
                clear:left;
                width:100%;
                padding:0;
                padding-top: 15px;
                background: rgb(245, 247, 247);
            }

            .navPage {
                width:100%;
                height:24px;
                margin-bottom:16px;
                padding:0;
                font-size:16px;
                font-weight: 700;
                /*line-height: 24px;*/
                font-family: Arial;
                background: rgb(245, 247, 247);
                color:gray;
                cursor:hand;
            }

            #logo {
                display: flex;
                position: absolute;
                left: 100;
                justify-content: center;
                grid-area: logo;
                background: transparent;
            }

            #reg-sp {
                padding: 0;
                display: flex;
                justify-content: center;

                grid-area: reg-sp;
                background: transparent;
            }

            #reg-llc {
                padding: 0;
                display: flex;
                justify-content: center;

                grid-area: reg-llc;
                background: transparent;
            }

            #reg-llc1 {
                padding: 0;
                display: flex;
                justify-content: center;

                grid-area: reg-llc1;
                background: transparent;
            }

            #reg-llc2 {
                padding: 0;
                display: flex;
                justify-content: center;

                grid-area: reg-llc2;
                background: transparent;
            }

            #reg-pe {
                padding: 0;
                display: flex;
                justify-content: center;

                grid-area: reg-pe;
                background: transparent;
            }

            #move-prev {
                padding: 0;
                display: flex;
                justify-content: left;

                grid-area: move-prev;
                background: transparent;
            }


            #move-next {
                padding: 0;
                display: flex;
                justify-content: right;

                grid-area: move-next;
                background: transparent;
            }

            #download {
                padding: 0;
                display: flex;
                justify-content: center;

                grid-area: download;
                background: transparent;
            }

            .btn-header {
                background: transparent;
                border: none;
                height: 56px;
                font-weight: 700;
                font-size: 16px;
                width:100%;
                outline: none;
                color:gray;
                cursor: hand;

            }

            .btn-header:active {
                transform: translateY(1px);
                filter: saturate(150%);
            }

            .btn-header:focus{
                border: 0px solid lightgray;
            }
            .btn-header:hover{
                border: 1px solid lightgray;
            }

            .item2{
                height: 100%;
                width: 100%;
                border: 1px solid #B3B3B3;
                color: black;
                background-color: transparent;
                font-weight: 700;
                font-size: 12px;
                padding:16px;
            }

            .background1{
                background-color: white;
                color: black;
            }


            input:focus {
                outline: none;
            }

            .comboButton:focus {
                outline: none;
            }

            .comboListOn {
                display: block;
                z-index: 1000;
            }


            .showbox {
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 5%;
                background-color:transparent;
            }

            .loader {
                position: relative;
                margin: 0px auto;
                width: 100px;
                height:100%;
                background: transparent;
            }

            .loader:before {
                content: '';
                display: block;
                padding-top: 100%;
            }

            .circular {
                -webkit-animation: rotate 2s linear infinite;
                animation: rotate 2s linear infinite;
                height: 100%;
                -webkit-transform-origin: center center;
                -ms-transform-origin: center center;
                transform-origin: center center;
                width: 100%;
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                margin: auto;
            }

            .path {
                stroke-dasharray: 1, 200;
                stroke-dashoffset: 0;
                -webkit-animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
                animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
                stroke-linecap: round;
            }
            @-webkit-keyframes
            rotate {  100% {
                          -webkit-transform: rotate(360deg);
                          transform: rotate(360deg);
                      }
            }
            @keyframes
            rotate {  100% {
                          -webkit-transform: rotate(360deg);
                          transform: rotate(360deg);
                      }
            }
            @-webkit-keyframes
            dash {  0% {
                        stroke-dasharray: 1, 200;
                        stroke-dashoffset: 0;
                    }
                    50% {
                        stroke-dasharray: 89, 200;
                        stroke-dashoffset: -35;
                    }
                    100% {
                        stroke-dasharray: 89, 200;
                        stroke-dashoffset: -124;
                    }
            }
            @keyframes
            dash {  0% {
                        stroke-dasharray: 1, 200;
                        stroke-dashoffset: 0;
                    }
                    50% {
                        stroke-dasharray: 89, 200;
                        stroke-dashoffset: -35;
                    }
                    100% {
                        stroke-dasharray: 89, 200;
                        stroke-dashoffset: -124;
                    }
            }
            @-webkit-keyframes
            color {  100%, 0% {
                         stroke: #d62d20;
                     }
                     40% {
                         stroke: #0057e7;
                     }
                     66% {
                         stroke: #008744;
                     }
                     80%, 90% {
                         stroke: #ffa700;
                     }
            }
            @keyframes
            color {  100%, 0% {
                         stroke: #d62d20;
                     }
                     40% {
                         stroke: #0057e7;
                     }
                     66% {
                         stroke: #008744;
                     }
                     80%, 90% {
                         stroke: #ffa700;
                     }
            }

            .header__nav-toggle {
                grid-area: toggle;
                margin-right: 20px;
                display: none;
                width: 32px;
                padding: 0;
                height: 55px;
                transform: rotate(0deg);
                transition: 0.5s ease-in-out;
            }
            .header__nav-toggle span {
                display: block;
                position: absolute;
                height: 2px;
                width: 100%;
                background: #333;
                border-radius: 9px;
                opacity: 1;
                left: 0;
                top: 20px;
                transform: rotate(0deg);
                transition: 0.25s ease-in-out;
            }
            .header__nav-toggle span:nth-child(2), .header__nav-toggle span:nth-child(3) {
                top: 27px;
            }
            .header__nav-toggle span:nth-child(4) {
                top: 34px;
            }
            .header__nav-toggle:hover span {
                background-color: #136CFA;
                transition: 0.8s;
            }
            .header__nav-toggle_open span:nth-child(1),
            .header__nav-toggle_open span:nth-child(4) {
              top: 18px;
              width: 0%;
              left: 50%;
            }
            .header__nav-toggle_open span:nth-child(2) {
              transform: rotate(45deg);
            }
            .header__nav-toggle_open span:nth-child(3) {
              transform: rotate(-45deg);
            }

            /*Mobile styles*/

            @media screen and (max-width: 950px) {
                #navList {
                    overflow: auto;
                    height: 98px;
                }
                #navWrapper {
                    padding: 50px 0 0 0;
                    text-align: center;
                    overflow: auto;
                }
                body {
                    grid-template-areas:
                        "header "
                        "nav"
                        "article";
                    grid-template-rows: 56px 180px 1fr;
                    grid-template-columns: 1fr;
                }

                #logo {
                    left: 0;
                }
                header {
                    grid-template-areas: "logo toggle"
                        ". reg-llc"
                        ". reg-pe"
                        ". reg-sp";
                    grid-template-columns: minmax(200px, 1fr) 1fr;
                    justify-items: end;
                }
                #pageHeader .visible {
                    display: none;
                    margin-right: 20px;
                }
                .header__nav-toggle {
                   display: block;
                }
                #dialog {
                    display: block !important;
                }
                #dialog > div{
                    height: 48px !important;
                    float: none !important;
                }
                #dialog .max-height {
                    height: auto !important;
                    margin: 10px 0;
                }
            }
        </style>
        <script>
            var sectionID =
                    "<?php
if (isset($_GET["ID"])) {
    echo $_GET["ID"];
} else {
    echo "";
}
?>";

            var name =
                    "<?php
if (isset($_GET["name"])) {
    echo $_GET["name"];
} else {
    echo "";
}
?>";

            var title =
                    "<?php
if (isset($_GET["title"])) {
    echo $_GET["title"];
} else {
    echo "";
}
?>";


        </script>
    </head>


    <body>

        <div id="showbox" class="showbox" style="display:none">
            <div class="loader"> <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                </svg> </div>
        </div>
        <script>
            (function (i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-46156385-1', 'cssscript.com');
            ga('send', 'pageview');

        </script>
        <header id="pageHeader">
            <div id="logo">
                <img src="images/logo.png" alt="logo" style="max-width:175px;margin-top:-10px">
            </div>

            <div id="reg-llc" class="visible">
                <button class="btn-header" onclick="run(764, 'reg-llc', 'Регистрация ООО')">Открыть ООО</button>
            </div>

            <div id="reg-pe" class="visible">
                <button class="btn-header" onclick="run(727, 'reg-pe', 'Регистрация ЧУП')">Открыть ЧУП</button>
            </div>

            <div id="reg-sp" class="visible">
                <button class="btn-header" onclick="run(728, 'reg-sp', 'Регистрация ИП')">Открыть ИП</button>
            </div>
            <div class="header__nav-toggle">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
             </div>

        </header>
        <article id="mainArticle">
            <div id="commands" style="display:none">
                <div id="move-prev">
                    <button class="btn-header" style="color: #136CFA; font-size: 12px;" onclick="prev()">НАЗАД</button>
                </div>
                <div id="download">

                </div>

                <div id="move-next">
                    <button class="btn-header" style="color: #136CFA; font-size: 12px;" onclick="next()">ДАЛЕЕ</button>
                </div>
            </div>

            <div id="dialog">
            </div>
        </article>
        <nav id="mainNav">
            <div id="navWrapper">
                <div id="navTitle">

                </div>
                <div id="navList">
                </div>
            </div>
        </nav>
        <!--<div id="siteAds">Ads</div>-->
        <!--<footer id="pageFooter">Footer</footer>-->
    </body>


</html>
