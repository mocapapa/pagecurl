<?php
/*

 高性能版のテーブルを式に逆置き換えで低速化
 アルゴリズム確認用

 test1: javaのアルゴリズムを再現, anti-aliasing無し

*/
define('W', 500); // 画像幅サイズ
define('H', 333); // 画像縦サイズ
define('URA', 220); // 裏紙の色

$img1_in = imagecreatefrompng('image1.png'); // 原画1 (上)
$img2_in = imagecreatefrompng('image2.png'); // 原画2 (下)
$img_out = imagecreatetruecolor(W, H); // 出力画像


$R = 56;                     // カール半径
$Q = (1.0 + 1.0/16.0) * $R;  // 影の長さ,半径の何倍の影

$mag = 80;                   // ハイライト強弱
$alpha = 0.55;               // 透明度


for ($m_mode = 0; $m_mode <= 2; $m_mode++) {
  for ($c_mode = 0; $c_mode <= 1; $c_mode++) {
/*
$m_mode = 2;                 // 0: 半透明, 1: 裏画像, 2: 裏紙
$c_mode = 1;                 // 0: カールなし, 1: カール
*/

$count = 0;                  // イメージファイル通番

$w = 0;                      // 回転座標系(u, v)におけるu座標方向の変数

// create table

$step = 32.0;   // $wのステップ
$E = W/2+$Q; // $wの最終値

$theta0 = M_PI / 3.0;                        // $theta の初期値
$K0 = W/2*cos($theta0)+H/2*sin($theta0); // $w の初期値

//for ($w = $K0; $w >= -$E - $step; $w -= $step) {
  //  print "$w ";

  $i = ($w + $E)/$step;
  $theta = ($step * $i * M_PI)/3/($K0 + $E);
  $sin = sin($theta);
  $cos = cos($theta);

  //  print "w=$w, i=$i, theta=$theta\n";

  $K = W/2*$cos + H/2*$sin; // (W/2, -H/2)をtheta回転した点のx座標＝回転系座標(u,v)	におけるuの最大値
  $k = 2*$w +M_PI*$R - $K; // めくった左端のu座標

  $time_start = getmicrotime();

  // アルゴリズムの説明
  //   基本的に画像ドットの色を決定するわけだから、物理画像ドットから遡り
  //   論理ドットの色を決定する。(その逆に論理ドットをスキャンすると物理
  //   ドットに穴があいたりする恐れがあるため)

  //   物理座標→回転座標(回転)→画像座標(逆回転)
  //     上記は論理的な話であり、実際には回転→逆回転しなくても
  //     物理＝論理の点が存在する。その領域は物理座標をそのまま(要シフト)
  //     画像座標として点の色を求める。

  //   物理座標系が基準
  //   物理座標系上の点(x, y)の全てについて以下を行う。
  //   (1) 物理座標(x, y)を回転座標(u, v)に変換
  //       回転座標系でエリアを判別
  //       裏が見えていない範囲か(area0)？
  //       表と裏の両方が見える範囲か(area4)？
  //       円筒の範囲か(area3 or area5)？
  //   (2) 画像座標においてピクセル値を取り出す
  //       領域によっては回転座標の逆変換を行い、画像座標を求める
  //       逆変換が不要な領域は物理座標をそのまま(シフト必要)画像座標とする


  for ($y = -H/2; $y < H/2; $y++) {
    for ($x = -W/2; $x < W/2; $x++) {

      // 物理座標から画像座標へ変換しておく
      //    物理座標(x, y)
      //       -W/2 <= x <= W/2-1
      //       -H/2 <= y <= H/2-1
      //    画像座標(ix, iy)
      //        0 <= ix <= W-1
      //       H-1 => iy >= 0
      $ix = $x + W/2;
      $iy = -$y + H/2 -1;

      //      print "x=$x, y=$y, ix=$ix, iy=$iy\n";

      // rgbの取出し
      //
      $color = imagecolorat($img1_in, $ix, $iy);
      $r = ($color & 0xff0000)>>16;
      $g = ($color & 0xff00)>>8;
      $b = ($color & 0xff);

      // 物理座標(x, y)から回転座標(u, v)への変換
      //
      $u = $x*$cos - $y*$sin;
      $v = $x*$sin + $y*$cos;

      // $p: 回転座標系(u, v)における、回転軸からの距離
      //     三角関数を引く際の引数となる
      $p = $u - $w;

      // エリアの決定
      // エリアは回転座標系上の領域と定義
      //
      if ((($c_mode == 0) && ($u <= $k)) || (($c_mode == 1) && ($p <= -$Q))) {
        // area0
        // 紙の影よりも左
        // ここは論理ピクセル＝物理ピクセルのため、何もしない

      } else if (($c_mode == 1) && ($p <= -$R)) {
        // areaX
        // カールモードでの影の領域, 何もしない

      } else if ($p <= 0) {
	// area1 or area4
	// 紙の重なりの部分
	if ($c_mode == 0) {
	  // 非カールモード
	  $u4 = 2*$w - $u + M_PI*$R;
	  $coeff = 1.0;
	} else {
	  // カールモード
	  $u4 = M_PI*$R + $R*asin(-$p/$R) + $w;
	  $coeff = $alpha + (1- $alpha)*cos(asin(-$p/$R));
	}
	// 回転座標を逆変換
	$x4 = $u4*$cos + $v*$sin;
	$y4 = -$u4*$sin + $v*$cos;

	if ((-W/2 <= $x4) && ($x4 < W/2) && (-H/2 <= $y4) && ($y4 < H/2)) {
	  // area4
	  $ix4 = $x4 + W/2;
	  $iy4 = -$y4 + H/2 -1;
	  if ($m_mode==2) {
	    // 裏紙
	    $r = $g = $b = URA*$coeff;
	  } else if ($m_mode == 0) {
	    // 半透明
	    $color = imagecolorat($img1_in, $ix4, $iy4);
	    $r4 = ($color & 0xff0000)>>16;
	    $g4 = ($color & 0xff00)>>8;
	    $b4 = ($color & 0xff);
	    $r = sqrt($r * $r4)*$coeff;
	    $g = sqrt($g * $g4)*$coeff;
	    $b = sqrt($b * $b4)*$coeff;

	  } else {
	    // 裏画像
	    $color = imagecolorat($img1_in, $ix4, $iy4);
	    $r4 = ($color & 0xff0000)>>16;
	    $g4 = ($color & 0xff00)>>8;
	    $b4 = ($color & 0xff);
	    $r = $r4*$coeff;
	    $g = $g4*$coeff;
	    $b = $b4*$coeff;

	  }

	} else {
	  // area1
	}
      } else if ($p <= $R) {
	// area3 or area2 or area5

	$u3 = M_PI*$R - $R*asin($p/$R) + $w;
	$x3 = $u3*$cos + $v*$sin;
	$y3 = -$u3*$sin + $v*$cos;

	$u2 = $R*asin($p/$R) + $w;
	$x2 = $u2*$cos + $v*$sin;
	$y2 = -$u2*$sin + $v*$cos;

	$coeff = $alpha + (1- $alpha)*cos(asin($p/$R));
	if ((-W/2 <= $x3) && ($x3 <= W/2) && (-H/2 <= $y3) && ($y3 <= H/2)) {
	  // area3
	  $ix3 = $x3 + W/2;
	  $iy3 = -$y3 + H/2 -1;
	  if ($m_mode==2) {
	    // 裏紙
	    $r = $g = $b = URA * $coeff;
	  } else if ($m_mode == 0){
	    // 半透明
	    $color = imagecolorat($img1_in, $ix3, $iy3);
	    $r3 = ($color & 0xff0000)>>16;
	    $g3 = ($color & 0xff00)>>8;
	    $b3 = ($color & 0xff);

	    $ix2 = ($x2 + W/2);
	    $iy2 = (-$y2 + H/2 -1);

	    $color = imagecolorat($img1_in, $ix2, $iy2);
	    $r2 = ($color & 0xff0000)>>16;
	    $g2 = ($color & 0xff00)>>8;
	    $b2 = ($color & 0xff);

	    $r = sqrt($r2 * $r3) * $coeff;
	    $g = sqrt($g2 * $g3) * $coeff;
	    $b = sqrt($b2 * $b3) * $coeff;

	  } else {
	    // 裏画像
	    $color = imagecolorat($img1_in, $ix3, $iy3);
	    $r = ($color & 0xff0000)>>16;
	    $g = ($color & 0xff00)>>8;
	    $b = ($color & 0xff);
	    $r = $r*$coeff;
	    $g = $g*$coeff;
	    $b = $b*$coeff;

	  }
	  // highlight
	  $tmp = cos(($p-$R/2)/($R/2)*(M_PI/2));
	  $tmp *= $tmp * $tmp * $tmp * $mag;
	  $c3 = $tmp > 0 ? $tmp : 0; // area3, high-light
	  $r += $c3;
	  $g += $c3;
	  $b += $c3;
	  if ($r >= 255) $r = 255;
	  if ($g >= 255) $g = 255;
	  if ($b >= 255) $b = 255;

	} else if ((-W/2 <= $x2) && ($x2 < W/2) && (-H/2 <= $y2) && ($y2 < H/2)) {
	  // area2
	  // 座標が画像の範囲なら重なり部分
	  $ix2 = $x2 + W/2;
	  $iy2 = -$y2 + H/2 -1;

	  $color = imagecolorat($img1_in, $ix2, $iy2);
	  $r2 = ($color & 0xff0000)>>16;
	  $g2 = ($color & 0xff00)>>8;
	  $b2 = ($color & 0xff);
	  $r = $r2*$coeff;
	  $g = $g2*$coeff;
	  $b = $b2*$coeff;

	} else {
	  // area5
	  // そうでなければ下の部分
	  $color = imagecolorat($img2_in, $ix, $iy);
	  $r = ($color & 0xff0000)>>16;
	  $g = ($color & 0xff00)>>8;
	  $b = ($color & 0xff);

	  $r *= $alpha;
	  $g *= $alpha;
	  $b *= $alpha;

	}
      } else if ($p <= $Q) {
	// area6
	// 次画像の影までの部分
	$color = imagecolorat($img2_in, $ix, $iy);
	$r = ($color & 0xff0000)>>16;
	$g = ($color & 0xff00)>>8;
	$b = ($color & 0xff);

	$tmp = cos(($p-$Q)/($Q-$R)*M_PI/2);
	$c2 = $alpha + (1- $alpha)*$tmp;

	$r *= $c2;
	$g *= $c2;
	$b *= $c2;

      } else if ($u <= $K) {
	// area7
	// 次画像の影から右の部分
	$color = imagecolorat($img2_in, $ix, $iy);
	$r = ($color & 0xff0000)>>16;
	$g = ($color & 0xff00)>>8;
	$b = ($color & 0xff);

      }	
      imagesetpixel($img_out, $ix, $iy, imagecolorclosest($img_out, $r, $g, $b));
    }
  }
  $filename = sprintf("NewImage-test1-%d-%d-%03d.png", $m_mode, $c_mode, $count++);
  imagepng($img_out, $filename,0);

  $time_end = getmicrotime();
  $time = $time_end - $time_start;
  echo "$time sec\n";

//}
  }
}

function getmicrotime() {
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$sec + (float)$usec);
}
