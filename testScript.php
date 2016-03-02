<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.11.15
 * Time: 17:21
 */

require __DIR__ . '/vendor/autoload.php';


$stringWithCaptcha = '
<!DOCTYPE HTML><html class="i-ua_js_no i-ua_css_standard"><head><meta charset="utf-8"/><meta http-equiv="X-UA-Compatible" content="IE=edge"/><title>Ой!</title><script>;(function(d,e,c,r,n,w,v,f){e=d.documentElement;c="className";r="replace";n="createElementNS";f="firstChild";w="http://www.w3.org/2000/svg";e[c]+=" i-ua_svg_"+(!!d[n]&&!!d[n](w,"svg").createSVGRect?"yes":"no");v=d.createElement("div");v.innerHTML="<svg/>";e[c]+=" i-ua_inlinesvg_"+((v[f]&&v[f].namespaceURI)==w?"yes":"no");})(document);;(function(d,e,c,r,n,w,v,f){e=d.documentElement;c="className";r="replace";n="createElementNS";f="firstChild";w="http://www.w3.org/2000/svg";e[c]+=!!d[n]&&!!d[n](w,"svg").createSVGRect?" i-ua_svg_yes":" i-ua_svg_no";v=d.createElement("div");v.innerHTML="<svg/>";e[c]+=(v[f]&&v[f].namespaceURI)==w?" i-ua_inlinesvg_yes":" i-ua_inlinesvg_no";})(document);;(function(d,e,c,r){e=d.documentElement;c="className";r="replace";e[c]=e[c][r]("i-ua_js_no","i-ua_js_yes");if(d.compatMode!="CSS1Compat")e[c]=e[c][r]("i-ua_css_standart","i-ua_css_quirks")})(document);</script><!--[if gt IE 9]><!--><link rel="stylesheet" href="/captcha/_common.css"/><!--<![endif]--><!--[if IE 6]><link rel="stylesheet" href="/captcha/_common.ie6.css"/><![endif]--><!--[if IE 7]><link rel="stylesheet" href="/captcha/_common.ie7.css"/><![endif]--><!--[if IE 8]><link rel="stylesheet" href="/captcha/_common.ie8.css"/><![endif]--><!--[if IE 9]><link rel="stylesheet" href="/captcha/_common.ie9.css"/><![endif]--></head><body class="b-page b-page_type_default i-ua i-ua_interaction_yes b-page__body i-global i-bem" data-bem="{&quot;b-page&quot;:{},&quot;i-ua&quot;:{},&quot;i-global&quot;:{&quot;lang&quot;:&quot;ru&quot;,&quot;tld&quot;:&quot;ru&quot;,&quot;content-region&quot;:&quot;ru&quot;,&quot;click-host&quot;:&quot;//clck.yandex.ru&quot;,&quot;passport-host&quot;:&quot;https://passport.yandex.ru&quot;,&quot;pass-host&quot;:&quot;//pass.yandex.ru&quot;,&quot;social-host&quot;:&quot;//social.yandex.ru&quot;,&quot;export-host&quot;:&quot;//export.yandex.ru&quot;,&quot;lego-static-host&quot;:&quot;//yastatic.net/lego/2.10-152&quot;}}"><div class="i-expander__gap"></div><div class="i-expander__content"><div class="island island_type_fly"><div class="badge badge_type_default"><div class="logo logo_lang_ru"><a class="logo__link" href="//www.yandex.ru"><img class="logo__image" alt="Яндекс" src="//yastatic.net/lego/_/La6qi18Z8LwgnZdsAr1qy1GwCwo.gif"/></a></div> </div><div class="content"><h1 class="title">ой...</h1><div class="text">
<p>Нам очень жаль, но&nbsp;запросы, поступившие с&nbsp;вашего IP-адреса, похожи на&nbsp;автоматические.
По&nbsp;этой причине мы&nbsp;вынуждены временно заблокировать доступ к&nbsp;поиску.</p>
<p>Чтобы&nbsp;продолжить поиск, пожалуйста, введите символы с&nbsp;картинки в&nbsp;поле ввода и&nbsp;нажмите &laquo;Отправить&raquo;.</p>
<p class="b-hidden"><i class="icon icon_alert_yes"></i>
<b>В вашем браузере отключены файлы cookies</b>. Яндекс не сможет запомнить вас и правильно идентифицировать в дальнейшем. Чтобы включить cookies, воспользуйтесь советами на <a class="link" target="_blank" href="//help.yandex.ru/common/?id=1111120">странице нашей Помощи</a>.
</p></div><div class="form form_state_image form_error_no form_audio_yes i-bem" data-bem="{&quot;form&quot;:{&quot;flash&quot;:&quot;/captcha/soundmanager2.swf&quot;,&quot;sound&quot;:&quot;http://market.yandex.ru/captcha/voice?aHR0cDovL25hLmNhcHRjaGEueWFuZGV4Lm5ldC92b2ljZT9rZXk9YzNzVE5NSG1VR1ZHZzhvRXJCRm1Zb2RNSW9tcmZYV2s,_0/1456919974/ec17d3e8abe612dc677250936aeece88_1f3b23bf4ebcf9c3d323e9a9490111b5&quot;,&quot;soundIntro&quot;:&quot;http://market.yandex.ru/captcha/voiceintro?aHR0cDovL25hLmNhcHRjaGEueWFuZGV4Lm5ldC9zdGF0aWMvaW50cm8tcnUubXAz_0/1456919974/ec17d3e8abe612dc677250936aeece88_22cd1cc15e10d8d8edb147b677e2fbf6&quot;,&quot;buttonPlay&quot;:&quot;Произнести&quot;,&quot;buttonPlaying&quot;:&quot;Воспроизводится&quot;}}"><form class="form__inner" method="get" action="/checkcaptcha"><input class="form__key" type="hidden" name="key" value="c3sTNMHmUGVGg8oErBFmYodMIomrfXWk_0/1456919974/ec17d3e8abe612dc677250936aeece88_98a367f8da1c333d2037cf65a23ebfce"/><input class="form__retpath" type="hidden" name="retpath" value="http://market.yandex.ru/product/10720908/offers?hid=90490&amp;grhow=shop_8380081d58e082a313a251645cee55e0"/><div class="form__trigger" title="Изображение &#8596; Звук" role="button" tabindex="0" aria-label="Изображение &#8596; Звук"></div><span class="link form__refresh" title="Показать другую картинку" aria-label="Показать другую картинку" role="button" tabindex="0"></span><img class="image form__captcha" style="background: #cfcfcf;" src="http://market.yandex.ru/captchaimg?aHR0cDovL25hLmNhcHRjaGEueWFuZGV4Lm5ldC9pbWFnZT9rZXk9YzNzVE5NSG1VR1ZHZzhvRXJCRm1Zb2RNSW9tcmZYV2s,_0/1456919974/ec17d3e8abe612dc677250936aeece88_f1f293d57579d4ab3098a8bff57f53fe" alt=""/><div class="form__audio"><button class="button button_size_m button_type_play button_theme_normal form__play i-bem" role="button" type="button" data-bem="{&quot;button&quot;:{}}"><span class="button__text">Произнести</span></button></div><div class="form__arrow">→</div><span class="input input_size_m input_clear_no input_keyboard_yes input_theme_normal form__input i-bem" data-bem="{&quot;input&quot;:{&quot;autoFocus&quot;:true,&quot;live&quot;:false}}"><label class="input__hint input__hint_visibility_visible" id="hintuniq14563230628132" for="uniq14563230628132" aria-hidden="true">символы слева</label><span class="input__box"><input class="input__control i-bem" id="rep" name="rep" data-bem="{&quot;input__control&quot;:{}}"/></span><span class="b-keyboard-loader b-keyboard-loader_type_search b-keyboard-loader_lang_ru i-bem" data-bem="{&quot;b-keyboard-loader&quot;:{&quot;for&quot;:&quot;#rep&quot;}}"><img class="image b-keyboard-loader__keyboard" src="//yastatic.net/lego/_/La6qi18Z8LwgnZdsAr1qy1GwCwo.gif" alt=""/></span></span><button class="button button_size_m button_side_right button_theme_normal form__submit i-bem" role="button" type="submit" data-bem="{&quot;button&quot;:{}}"><span class="button__text">Отправить</span></button></form></div></div><div class="why"><h2 class="why__title">Почему так случилось?</h2>
    <p>Возможно, автоматические запросы принадлежат не вам, а другому пользователю, выходящему в сеть с одного с вами IP-адреса.
    Вам необходимо один раз ввести символы в форму, после чего мы запомним вас и сможем отличать от других пользователей, выходящих с данного IP.
    В этом случае страница с капчей не будет беспокоить вас довольно долго.</p>
    <p>Возможно, в вашем браузере установлены дополнения, которые могут задавать автоматические запросы к поиску. В этом случае рекомендуем вам отключить их.</p>
    <p>Также возможно, что ваш компьютер заражен вирусной программой, использующей его для сбора информации.
    Может быть, вам стоит проверить систему на наличие вирусов, например, антивирусной утилитой <a class="link" target="_blank" href="http://www.freedrweb.com/?lng=ru">CureIt</a> от «Dr.Web».</p>
    <p>Если у вас возникли проблемы или вы хотите задать вопрос нашей службе поддержки, пожалуйста, воспользуйтесь
    <a class="link" href="//feedback2.yandex.ru/marketcaptcha/">формой обратной связи</a>.
    </p>
</div><div class="note"><p>
    Если автоматические запросы действительно поступают с вашего компьютера, и вы об этом знаете (например, вам по роду деятельности необходимо отправлять Яндексу
    подобные запросы), рекомендуем воспользоваться специально разработанным для этих целей сервисом
    <a class="link" href="//api.yandex.ru/market">API Яндекс.Маркета</a>.
</p></div></div></div><div class="popup popup_theme_ffffff popup_color_error popup_autoclosable_yes popup_adaptive_yes popup_animate_yes"><div class="popup__under"></div><i class="popup__tail"></i><div class="popup__content">Неверно, попробуйте ещё раз.</div></div><script src="//yastatic.net/jquery/1.8.3/jquery.min.js"></script><script src="/captcha/soundmanager2.min.js"></script><script src="/captcha/_common.ru.js"></script>
<!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter10630330 = new Ya.Metrika({id:10630330, webvisor:true, clickmap:true, trackLinks:true, accurateTrackBounce:true, ut:"noindex"}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/10630330?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->
</body></html>';

//$stringSimple = ''


$parser = new \Sunra\PhpSimple\HtmlDomParser();

$dom = $parser->str_get_html($stringWithCaptcha);

if (strpos($dom->innertext,"вынуждены временно заблокировать")) {
	echo "Это капча!";
} else {
	echo "Это страница с данными!";
}
