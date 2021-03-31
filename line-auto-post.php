<?php

/*
  Plugin Name: Post to LINE Official Account
  Plugin URI: https://www.shipweb.jp/
  Description: Post to LINE Officail Account too when Post to WordPress. This plugin is based on LINE AUTO POST by growniche.
  Version: 1.0.0
  Author: ship
  Author URI: https://www.shipweb.jp/
  License: GPLv3
*/

/*  Copyright 2020 ship (email : shipwebdotjp@gmail.com)
    https://www.gnu.org/licenses/gpl-3.0.txt

*/

// WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションに、
// LineAutoPostクラスのインスタンスを生成するStatic関数をフック
add_action('init', 'post2lineoa::instance');

class post2lineoa {

    /**
     * このプラグインのバージョン
     */
    const VERSION = '1.0.0';

    /**
     * このプラグインのID：Ship Post to Line Officail Account
     */
    const PLUGIN_ID = 'sp2loa';

    /**
     * Credentialプレフィックス
     */
    const CREDENTIAL_PREFIX = self::PLUGIN_ID . '-nonce-action_';

    /**
     * CredentialAction：設定
     */
    const CREDENTIAL_ACTION__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-action_settings-form';

    /**
     * CredentialAction：投稿
     */
    const CREDENTIAL_ACTION__POST = self::PLUGIN_ID . '-nonce-action_post';

    /**
     * CredentialName：設定
     */
    const CREDENTIAL_NAME__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-name_settings-form';

    /**
     * CredentialName：投稿
     */
    const CREDENTIAL_NAME__POST = self::PLUGIN_ID . '-nonce-name_post';

    /**
     * (23文字)
     */
    const PLUGIN_PREFIX = self::PLUGIN_ID . '_';

    /**
     * OPTIONSテーブルのキー：ChannelAccessToken
     */
    const OPTION_KEY__CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'channel-access-token';

    /**
     * OPTIONSテーブルのキー：ChannelSecret
     */
    const OPTION_KEY__CHANNEL_SECRET = self::PLUGIN_PREFIX . 'channel-secret';

    /**
     * 画面のslug：トップ
     */
    const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

    /**
     * 画面のslug：初期設定
     */
    const SLUG__INITIAL_CONFIG_FORM = self::PLUGIN_PREFIX . 'initial-config-form';

    /**
     * パラメータ名：ChannelAccessToken
     */
    const PARAMETER__CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'channel-access-token';

    /**
     * パラメータ名：ChannelSecret
     */
    const PARAMETER__CHANNEL_SECRET = self::PLUGIN_PREFIX . 'channel-secret';

    /**
     * パラメータ名：LINEメッセージ送信チェックボックス
     */
    const PARAMETER__SEND_CHECKBOX = self::PLUGIN_PREFIX . 'send-checkbox';

    /**
     * TRANSIENTキー(一時入力値)：ChannelAccessToken ※4文字+41文字以下
     */
    const TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'temp-channel-access-token';

    /**
     * TRANSIENTキー(一時入力値)：ChannelSecret ※4文字+41文字以下
     */
    const TRANSIENT_KEY__TEMP_CHANNEL_SECRET = self::PLUGIN_PREFIX . 'temp-channel-secret';

    /**
     * TRANSIENTキー(不正メッセージ)：ChannelAccessToken
     */
    const TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'invalid-channel-access-token';

    /**
     * TRANSIENTキー(不正メッセージ)：ChannelSecret
     */
    const TRANSIENT_KEY__INVALID_CHANNEL_SECRET = self::PLUGIN_PREFIX . 'invalid-channel-secret';

    /**
     * TRANSIENTキー(エラー)：LINEメッセージ送信失敗
     */
    const TRANSIENT_KEY__ERROR_SEND_TO_LINE = self::PLUGIN_PREFIX . 'error-send-to-line';

    /**
     * TRANSIENTキー(成功)：LINEメッセージ送信成功
     */
    const TRANSIENT_KEY__SUCCESS_SEND_TO_LINE = self::PLUGIN_PREFIX . 'success-send-to-line';

    /**
     * TRANSIENTキー(保存完了メッセージ)：設定
     */
    const TRANSIENT_KEY__SAVE_SETTINGS = self::PLUGIN_PREFIX . 'save-settings';

    /**
     * TRANSIENTのタイムリミット：5秒
     */
    const TRANSIENT_TIME_LIMIT = 5;

    /**
     * 通知タイプ：エラー
     */
    const NOTICE_TYPE__ERROR = 'error';

    /**
     * 通知タイプ：警告
     */
    const NOTICE_TYPE__WARNING = 'warning';

    /**
     * 通知タイプ：成功
     */
    const NOTICE_TYPE__SUCCESS = 'success';

    /**
     * 通知タイプ：情報
     */
    const NOTICE_TYPE__INFO = 'info';

    /**
     * 暗号化する時のパスワード：STRIPEの公開キーとシークレットキーの複合化で使用
     */
    const ENCRYPT_PASSWORD = 's9YQReXd';

    /**
     * 正規表現：ChannelAccessToken
     */
    const REGEXP_CHANNEL_ACCESS_TOKEN = '/^[a-zA-Z0-9+\/=]{100,}$/';
    /**
     * 正規表現：ChannelSecret
     */
    const REGEXP_CHANNEL_SECRET = '/^[a-z0-9]{30,}$/';

    /**
     * WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションにフックする、
     * SimpleStripeCheckoutクラスのインスタンスを生成するStatic関数
     */
    static function instance() {
        return new self();
    }

    /**
     * 複合化：AES 256
     * @param edata 暗号化してBASE64にした文字列
     * @param string 複合化のパスワード
     * @return 複合化された文字列
     */
    static function decrypt($edata, $password) {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);
        $rounds = 3; // depends on key length
        $data00 = $password.$salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32,16);
        return openssl_decrypt($ct, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * crypt AES 256
     *
     * @param data $data
     * @param string $password
     * @return base64 encrypted data
     */
    static function encrypt($data, $password) {
        // Set a random salt
        $salt = openssl_random_pseudo_bytes(16);
        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
          $dx = hash('sha256', $dx.$password.$salt, true);
          $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($salt . $encrypted_data);
    }

    /**
     * HTMLのOPTIONタグを生成・取得
     */
    static function makeHtmlSelectOptions($list, $selected, $label = null) {
        $html = '';
        foreach ($list as $key => $value) {
            $html .= '<option class="level-0" value="' . $key . '"';
            if ($key == $selected) {
                $html .= ' selected="selected';
            }
            $html .= '">' . (is_null($label) ? $value : $value[$label]) . '</option>';
        }
        return $html;
    }

    /**
     * 通知タグを生成・取得
     * @param message 通知するメッセージ
     * @param type 通知タイプ(error/warning/success/info)
     * @retern 通知タグ(HTML)
     */
    static function getNotice($message, $type) {
        return 
            '<div class="notice notice-' . $type . ' is-dismissible">' .
            '<p><strong>' . esc_html($message) . '</strong></p>' .
            '<button type="button" class="notice-dismiss">' .
            '<span class="screen-reader-text">Dismiss this notice.</span>' .
            '</button>' .
            '</div>';
    }

    /**
     * コンストラクタ
     */
    function __construct() {
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (is_super_admin() || current_user_can('administrator') || current_user_can('editor') || current_user_can('author')) {
            // 投稿(公開)した時のコールバック関数を定義
            add_action('publish_post', [$this, 'send_to_line'], 1, 6);
            // 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
            add_action('admin_notices', [$this, 'error_send_to_line']);
            // 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
            add_action('admin_notices', [$this, 'success_send_to_line']);
            // 投稿画面にチェックボックスを表示
            add_action('add_meta_boxes', [$this, 'add_send_checkbox'], 10, 2 );
        }
        // 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
        if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator'))) {
            // 管理画面のトップメニューページを追加
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            // 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
            // 初期設定を保存する関数をフック
            add_action('admin_init', [$this, 'save_settings']);
        }
    }

    function add_send_checkbox() {
        add_meta_box(
            // チェックボックスのID
            self::PARAMETER__SEND_CHECKBOX,
            // チェックボックスのラベル名
            'Post to LINE Official',
            // チェックボックスを表示するコールバック関数
            [$this, 'show_send_checkbox'],
            // 投稿画面に表示
            'post',
            // 投稿画面の右サイドに表示
            'side',
            // 優先度(最優先)
            'high'
        );
    }

    /**
     * LINEにメッセージを送信するチェックボックスを表示
     */
    function show_send_checkbox() {
        // nonceフィールドを生成・取得
        $nonce_field = wp_nonce_field(
            self::CREDENTIAL_ACTION__POST,
            self::CREDENTIAL_NAME__POST,
            true,
            false
        );
        if (get_post_status(get_the_ID()) === 'publish') {
			$checked = '';
		}else{
			$checked = 'checked';
		}
        echo
            '<p>' .
            $nonce_field .
            '<input type="checkbox" name="' . self::PARAMETER__SEND_CHECKBOX . '" value="ON" id="post2lineoa" '.$checked.'>' .
            '<label for="post2lineoa">Send notify through LINE Official</label>' .
            '</p>';
    }

    /**
     * LINEメッセージを送信
     */
    function send_to_line($post_ID, $post){
        // ログインしていない場合は無視
        if (!is_user_logged_in()) return;
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (!is_super_admin() && !current_user_can('administrator') && !current_user_can('editor') && !current_user_can('author')) return;
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (!isset($_POST[self::CREDENTIAL_NAME__POST]) || !$_POST[self::CREDENTIAL_NAME__POST]) return;
        // nonceで設定したcredentialのチェック結果に問題がある場合
        if (!check_admin_referer(self::CREDENTIAL_ACTION__POST, self::CREDENTIAL_NAME__POST)) return;
        // LINEメッセージ送信チェックボックスにチェックがない場合は無視
        if ($_POST[self::PARAMETER__SEND_CHECKBOX] != 'ON') return;
        // ChannelAccessTokenをOPTIONSテーブルから取得
        $channel_access_token = self::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
        // ChannelSecretをOPTIONSテーブルから取得
        $channel_secret = self::decrypt(get_option(self::OPTION_KEY__CHANNEL_SECRET), self::ENCRYPT_PASSWORD);
        // ChannelAccessTokenとChannelSecretが設定されている場合
        if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0) {
    		// 投稿のタイトルを取得
    		$title = sanitize_text_field($post->post_title);
            // 投稿のタイトルの先頭40文字取得
            if(mb_strlen($title) > 40){
                $title = mb_substr($title, 0, 39)."…";
            }

            // 投稿の本文を取得
            $body = preg_replace("/( |　|\n|\r)/", "", strip_tags(sanitize_text_field($post->post_content)));
            if(mb_strlen($body) > 60){
                // 投稿の本文の先頭60文字取得
                $body = mb_substr($body, 0, 59)."…";
            }
            if(mb_strlen($body) == 0){
                $body = " ";
            }

            // 投稿のURLを取得
    		$link = get_permalink($post_ID);

            // 投稿のサムネイルを取得
            $thumb = get_the_post_thumbnail_url($post_ID);
            if(substr($thumb,0,5) != "https"){
                $thumb = "";
            }

    		// 本文を作成
    		$alttext = $title . "\r\n" . $body . "\r\n" . $link;
    		
    		// LINEに送信
    		require_once(dirname(__FILE__).'/vendor/autoload.php');
    		
            if($thumb != ""){
                //サムネイル画像のImageコンポーネント
                $thumbImageComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder($thumb,NULL,NULL,NULL,NULL,'100%','16:9','cover');

                //ヒーローブロック
                $thumbBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$thumbImageComponent],NULL,NULL,'none');
                $thumbBoxComponent->setPaddingAll('none');

            }else{
                $thumbBoxComponent = NULL;
            }

            //タイトルのTextコンポーネント
            $titleTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($title,NULL,NULL,NULL,NULL,NULL,TRUE,2,'bold',NULL,NULL);
            //ヘッダーブロック
            $titleBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$titleTextComponent],NULL,NULL,'none');
            //$titleBoxComponent->setBackgroundColor("#FFFFFF");
            $titleBoxComponent->setPaddingTop('xl');
            $titleBoxComponent->setPaddingBottom('xs');
            $titleBoxComponent->setPaddingStart('xl');
            $titleBoxComponent->setPaddingEnd('xl');        
            
            //本文のTextコンポーネント
            $bodyTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($body,NULL,NULL,NULL,NULL,NULL,TRUE,3,NULL,NULL,NULL);

            //ボディブロック
            $bodyBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$bodyTextComponent],NULL,NULL,'none');
            $bodyBoxComponent->setPaddingBottom('none');
            $bodyBoxComponent->setPaddingTop('xs');
            $bodyBoxComponent->setPaddingStart('xl');
            $bodyBoxComponent->setPaddingEnd('xl');  

            //リンクアクションコンポーネント
            $linkActionBuilder = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("Read more",$link);

            //リンクのボタンコンポーネント
            $linkButtonComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder($linkActionBuilder,NULL,NULL,NULL,'link',NULL,NULL);
            
            //フッターブロック
            $footerBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$linkButtonComponent],NULL,NULL,'none');
            $footerBoxComponent->setPaddingTop('none');

            //ブロックスタイル
            $blockStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BlockStyleBuilder("#FFFFFF");

            //バブルスタイル
            $bubbleStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder($blockStyleBuilder,$blockStyleBuilder,$blockStyleBuilder,$blockStyleBuilder);

            //バブルコンテナ
            $bubbleContainerBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder(NULL, $thumbBoxComponent, $titleBoxComponent,$bodyBoxComponent,$footerBoxComponent,$bubbleStyleBuilder);

            //Flexメッセージ
            $flexMessageBuilder =  new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($alttext, $bubbleContainerBuilder);

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            
			//メッセージビルダーをブロードキャスト
            $response = $bot->broadcast($flexMessageBuilder);
            
            // 送信に成功した場合
            if ($response->getHTTPStatus() === 200) {
                // LINE送信に成功した旨をTRANSIENTに5秒間保持
                set_transient(self::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE, 'LINEに送信しました', self::TRANSIENT_TIME_LIMIT);
            }
            // 送信に失敗した場合
            else {
                // LINE送信に失敗した旨をTRANSIENTに5秒間保持
                set_transient(self::TRANSIENT_KEY__ERROR_SEND_TO_LINE, 'LINEへの送信に失敗しました。' . $response->getRawBody(), self::TRANSIENT_TIME_LIMIT);
            }
        }
    }

    /**
     * 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
     */
    function error_send_to_line() {
        // LINE送信に失敗した旨のメッセージをTRANSIENTから取得
        if (false !== ($error_send_to_line = get_transient(self::TRANSIENT_KEY__ERROR_SEND_TO_LINE))) {
            echo self::getNotice($error_send_to_line, self::NOTICE_TYPE__ERROR);
        }
    }

    /**
     * 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
     */
    function success_send_to_line() {
        // LINE送信に成功した旨のメッセージをTRANSIENTから取得
        if (false !== ($success_send_to_line = get_transient(self::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE))) {
            echo self::getNotice($success_send_to_line, self::NOTICE_TYPE__SUCCESS);
        }
    }

    /**
     * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
     * 管理画面のトップメニューページを追加する関数
     */
    function set_plugin_menu() {
        // 設定メニューへ「Post to LINE」を追加
        add_options_page(
            // ページタイトル：
            'Preferences for Post to LINE',
            // メニュータイトル：
            'Post to LINE',
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            self::SLUG__SETTINGS_FORM,
            // メニューに紐づく画面を描画するcallback関数：
            [$this, 'show_settings']
        );
    }

    /**
     * 初期設定画面を表示
     */
    function show_settings() {
        // 初期設定の保存完了メッセージ
        if (false !== ($complete_message = get_transient(self::TRANSIENT_KEY__SAVE_SETTINGS))) {
            $complete_message = self::getNotice($complete_message, self::NOTICE_TYPE__SUCCESS);
        }
        // ChannelAccessTokenの不正メッセージ
        if (false !== ($invalid_channel_access_token = get_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN))) {
            $invalid_channel_access_token = self::getNotice($invalid_channel_access_token, self::NOTICE_TYPE__ERROR);
        }
        // ChannelSecretの不正メッセージ
        if (false !== ($invalid_channel_secret = get_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_SECRET))) {
            $invalid_channel_secret = self::getNotice($invalid_channel_secret, self::NOTICE_TYPE__ERROR);
        }
        // ChannelAccessTokenのパラメータ名
        $param_channel_access_token = self::PARAMETER__CHANNEL_ACCESS_TOKEN;
        // ChannelSecretのパラメータ名
        $param_channel_secret = self::PARAMETER__CHANNEL_SECRET;
        // ChannelAccessTokenをTRANSIENTから取得
        if (false === ($channel_access_token = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN))) {
            // 無ければoptionsテーブルから取得
            $channel_access_token = self::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
        }
        $channel_access_token = esc_html($channel_access_token);
        // ChannelSecretをTRANSIENTから取得
        if (false === ($channel_secret = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_SECRET))) {
            // 無ければoptionsテーブルから取得
            $channel_secret = self::decrypt(get_option(self::OPTION_KEY__CHANNEL_SECRET), self::ENCRYPT_PASSWORD);
        }
        $channel_secret = esc_html($channel_secret);
        // nonceフィールドを生成・取得
        $nonce_field = wp_nonce_field(self::CREDENTIAL_ACTION__SETTINGS_FORM, self::CREDENTIAL_NAME__SETTINGS_FORM, true, false);
        // 送信ボタンを生成・取得
        $submit_button = get_submit_button('Save');
        // HTMLを出力
        echo <<< EOM
            <div class="wrap">
            <h2>Preferences</h2>
            {$complete_message}
            {$invalid_channel_access_token}
            {$invalid_channel_secret}
            <form action="" method='post' id="line-auto-post-settings-form">
                {$nonce_field}
                <p>
                    <label for="{$param_channel_access_token}">Channel Access Token：</label>
                    <input type="text" name="{$param_channel_access_token}" value="{$channel_access_token}"/>
                </p>
                <p>
                    <label for="{$param_channel_secret}">Channel Secret：</label>
                    <input type="text" name="{$param_channel_secret}" value="{$channel_secret}"/>
                </p>
                {$submit_button}
            </form>
            </div>
EOM;
    }

    /**
     * 初期設定を保存するcallback関数
     */
    function save_settings() {
        // nonceで設定したcredentialをPOST受信した場合
        if (isset($_POST[self::CREDENTIAL_NAME__SETTINGS_FORM]) && $_POST[self::CREDENTIAL_NAME__SETTINGS_FORM]) {
            // nonceで設定したcredentialのチェック結果が問題ない場合
            if (check_admin_referer(self::CREDENTIAL_ACTION__SETTINGS_FORM, self::CREDENTIAL_NAME__SETTINGS_FORM)) {
                // ChannelAccessTokenをPOSTから取得
                $channel_access_token = trim(sanitize_text_field($_POST[self::PARAMETER__CHANNEL_ACCESS_TOKEN]));
                // ChannelSecretをPOSTから取得
                $channel_secret = trim(sanitize_text_field($_POST[self::PARAMETER__CHANNEL_SECRET]));
                $valid = true;
                // ChannelAccessTokenが正しくない場合
                if (!preg_match(self::REGEXP_CHANNEL_ACCESS_TOKEN, $channel_access_token)) {
                    // ChannelAccessTokenの設定し直しを促すメッセージをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN, "Channel Access Token が正しくありません。", self::TRANSIENT_TIME_LIMIT);
                    // 有効フラグをFalse
                    $valid = false;
                }
                // ChannelSecretが正しくない場合
                if (!preg_match(self::REGEXP_CHANNEL_SECRET, $channel_secret)) {
                    // ChannelSecretの設定し直しを促すメッセージをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_SECRET, "Channel Secret が正しくありません。", self::TRANSIENT_TIME_LIMIT);
                    // 有効フラグをFalse
                    $valid = false;
                }
                // 有効フラグがTrueの場合(ChannelAccessTokenとChannelSecretが入力されている場合)
                if ($valid) {
                    // 保存処理
                    // ChannelAccessTokenをoptionsテーブルに保存
                    update_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN, self::encrypt($channel_access_token, self::ENCRYPT_PASSWORD));
                    // ChannelSecretをoptionsテーブルに保存
                    update_option(self::OPTION_KEY__CHANNEL_SECRET, self::encrypt($channel_secret, self::ENCRYPT_PASSWORD));
                    // 保存が完了したら、完了メッセージをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__SAVE_SETTINGS, "初期設定の保存が完了しました。", self::TRANSIENT_TIME_LIMIT);
                    // (一応)ChannelAccessTokenの不正メッセージをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN);
                    // (一応)ChannelSecretの不正メッセージをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_SECRET);
                    // (一応)ユーザが入力したChannelAccessTokenをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN);
                    // (一応)ユーザが入力したChannelSecretをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_SECRET);
                }
                // 有効フラグがFalseの場合(ChannelAccessToken、ChannelSecretが入力されていない場合)
                else {
                    // ユーザが入力したChannelAccessTokenをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN, $channel_access_token, self::TRANSIENT_TIME_LIMIT);
                    // ユーザが入力したChannelSecretをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_SECRET, $channel_secret, self::TRANSIENT_TIME_LIMIT);
                    // (一応)初期設定の保存完了メッセージを削除
                    delete_transient(self::TRANSIENT_KEY__SAVE_SETTINGS);
                }
                // 設定画面にリダイレクト
                wp_safe_redirect(menu_page_url(self::SLUG__SETTINGS_FORM), 303);
            }
        }
    }

} // end of class


?>