# Post to LINE Official Account
Wordpressの投稿をLINE公式アカウントに友達登録している人へ通知するためのプラグインです。  
Growniche社の[LINE AUTO POST](https://s-page.biz/line-auto-post/#home)を元に改変したものです。  
## オリジナルとの違い  
* 投稿画面での送信するかどうかのチェックボックスが右カラムに表示される
* 新規投稿の場合は自動的に送信チェックボックスにチェックが付く
* 管理画面での設定メニューがトップメニューではなく設定メニュのサブメニューとして表示される
* 通知メッセージをFlexメッセージに変更し、アイキャッチ画像を含めたレイアウトで通知

## インストール方法
1. [GitHub](https://github.com/shipwebdotjp/post2lineoa/releases)より最新版のZIPファイルをダウンロードします。
2. Wordpressの管理画面へログインし、「プラグイン」メニューから「新規追加」を選び、「プラグインをアップロード」をクリックします。
3. 「ファイルの選択」から、ダウンロードしておいたZIPファイルを選択し、「今すぐインストール」をクリックします。
4. インストールが完了したら、プラグイン一覧画面より「	
Post to LINE Official Account」を有効化します。

## 初期設定
1. あらかじめ[LINE Developers](https://developers.line.biz/)にて、公式アカウントのMessaging APIチャネルを作成しておいてください。
2. チャネルシークレットをチャネル基本設定から、チャネルアクセストークン（長期）をMessaging API設定より取得します。
3. Wordpressの管理画面より、設定メニューから「Post to LINE」をクリックしてPreferences画面を開きます。
4. Channel Access Tokenにチャネルアクセストークン（長期）を、 Channel Secretにチャネルシークレットをそれぞれコピペして「Save」します。

## 使用方法
1. 投稿画面で、右カラムに「Post to LINE Official」ボックスが表示されるので、通知したい場合は「Send notify through LINE Official」へチェックを入れて投稿を行います。  
※チェックが入っていると新規投稿だけでなく更新する場合でも通知されますので注意してください。
2. 画像付きで通知させたい場合は、右カラムのアイキャッチ画像を設定してください。

## スクリーンショット
友達登録している人へこのように通知されます。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttoline.png" width="320">  
リンクテキストや背景色、サムネのアスペクト比をカスタマイズすることも可能です。  
<img src="https://blog.shipweb.jp/wp-content/uploads/2021/03/PNG-imageposttolinecustom.png" width="320">  

## カスタマイズ
line-auto-post.phpを編集することでいくつかのカスタマイズが行えます。
* 通知メッセージ中のリンクラベルを変えたい場合は下記の'Read more'の部分を変更  
```
    const PARAMETER__READ_MORE_LABEL = 'Read more';
```
* 通知メッセージ中の画像領域のアスペクト比を変えたい場合は下記の'16:9'の部分を変更  
```
    const PARAMETER__IMAGE_ASPECTRATE = '16:9';
```

* 通知メッセージ中の背景色を変えたい場合は下記の'#FFFFFF'の部分を変更  
```
    const PARAMETER__TILE_BACKGROUND_COLOR = "#FFFFFF";
```

その他さまざまなカスタマイズを有償で承ります。[連絡先はこちら](https://blog.shipweb.jp/contact)

# 必要動作環境
* Wordpress  4.9.13以上

# 制作者
* ship [blog](https://blog.shipweb.jp/)


# 謝辞
* 素晴らしいプラグイン「LINE AUTO POST」を開発してくださったGrowniche社の方々

# ライセンス
GPLv3