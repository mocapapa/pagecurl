# pagecurl applet project #

  * PHP: for algorithm investigation and table generation
  * Java: Execution on the page

![http://pagecurl.googlecode.com/svn-history/r10/trunk/pagecurl.png](http://pagecurl.googlecode.com/svn-history/r10/trunk/pagecurl.png) <br />
<br>
<h3>モードの種類</h3>
非カール・透過モード　　　　　　　　　　カール・透過モード<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/output/test2-0-0-000.png' />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/output/test2-0-1-000.png' /><br />

非カール・裏画像モード　　　　　　　　　　カール・裏画像モード<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/output/test2-1-0-000.png' />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/output/test2-1-1-000.png' /><br />

非カール・裏紙モード　　　　　　　　　　カール・裏紙モード<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/output/test2-2-0-000.png' />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/output/test2-2-1-000.png' /><br />

<h3>アルゴリズム</h3>
<img src='http://pagecurl.googlecode.com/svn/trunk/algorithm.png' />
<br>
<br>
<br>
<h3>プログラム</h3>
<ul><li>test1.php: アルゴリズム検討用。関数テーブル化未対応。アンチエイリアス未対応。<br /></li></ul>

test1.phpの出力の一部:<br />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test1.png' />
⇨<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test1x4.png' />

(174,254)-(265,341)を切り取り、x4したもの。<br>
<br>
<br>
<br>
<br>

<ul><li>test2.php: test1.phpに画像アンチエイリアシングを実施。ただし境界は行なっていない。<br />
test2.phpの出力の一部:<br />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test2.png' />
⇨<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test2x4.png' /></li></ul>

<br>
<br>
<br>
<br>

<ul><li>test3.php: test2.phpに、境界も一部アンチエイリアシング実施。<br />
test3.phpの出力の一部:<br />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test3.png' />
⇨<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test3x4.png' /></li></ul>

<br>
<br>
<br>
<br>
<ul><li>test4.php: test1.phpに画像オーバサンプリング(x2)を実施。境界のオーバサンプリングも行なう。境界はうまく行ったが、画像部分の鮮明度が下がった。これは画像縮小時に単純に色の平均をとっているため、ローパスフィルタがかかっていると考えられる。</li></ul>

test4.phpの出力の一部:<br />
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test4.png' />
⇨<br>
<img src='http://pagecurl.googlecode.com/svn/trunk/php/x4/test4x4.png' />

サンプル画像ではOKだが、自然画像ではNG?