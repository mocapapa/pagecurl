<?php
/*

 高性能版のテーブルを式に逆置き換えで低速化
 アルゴリズム確認用

 test4: 元画像を2倍オーバサンプリングする

*/
define('W', 500); // 画像幅サイズ
define('H', 333); // 画像縦サイズ
define('URA', 220); // 裏紙の色

$img1_in = imagecreatefrompng('image1.png'); // 原画1
$img2_in = imagecreatefrompng('image2.png'); // 原画2
$img1 = imagecreatetruecolor(W+1, H+1);      // 原画1+1line
$img2 = imagecreatetruecolor(W+1, H+1);      // 原画2+1line
$img1x2_in = imagecreatetruecolor(W*2, H*2); // 2倍画像1
$img2x2_in = imagecreatetruecolor(W*2, H*2); // 2倍画像2

imagecopy($img1, $img1_in, 0, 0, 0, 0, W, H);
imagecopy($img2, $img2_in, 0, 0, 0, 0, W, H);

//imagepng($img1, 'img1',0);
//imagepng($img2, 'img2',0);
//exit;

for ($x = 0; $x < W; $x++) {
  for ($y = 0; $y < H; $y++) {
    $color1 = imagecolorat($img1, $x, $y);
    $r1 = ($color1 & 0xff0000)>>16;
    $g1 = ($color1 & 0xff00)>>8;
    $b1 = ($color1 & 0xff);
    $color2 = imagecolorat($img1, $x+1, $y);
    $r2 = ($color2 & 0xff0000)>>16;
    $g2 = ($color2 & 0xff00)>>8;
    $b2 = ($color2 & 0xff);
    $color3 = imagecolorat($img1, $x, $y+1);
    $r3 = ($color3 & 0xff0000)>>16;
    $g3 = ($color3 & 0xff00)>>8;
    $b3 = ($color3 & 0xff);
    $color4 = imagecolorat($img1, $x+1, $y+1);
    $r4 = ($color4 & 0xff0000)>>16;
    $g4 = ($color4 & 0xff00)>>8;
    $b4 = ($color4 & 0xff);
    
    $ro1=($r1+$r2)/2.0;
    $go1=($g1+$g2)/2.0;
    $bo1=($b1+$b2)/2.0;
    $ro2=($r1+$r3)/2.0;
    $go2=($g1+$g3)/2.0;
    $bo2=($b1+$b3)/2.0;
    $ro3=($r1+$r2+$r3+$r4)/4.0;
    $go3=($g1+$g2+$g3+$g4)/4.0;
    $bo3=($b1+$b2+$b3+$b4)/4.0;
    
    imagesetpixel($img1x2_in, $x*2,   $y*2,   imagecolorclosest($img1x2_in, $r1, $g1, $b1));
    imagesetpixel($img1x2_in, $x*2+1, $y*2,   imagecolorclosest($img1x2_in, $ro1, $go1, $bo1));
    imagesetpixel($img1x2_in, $x*2,   $y*2+1, imagecolorclosest($img1x2_in, $ro2, $go2, $bo2));
    imagesetpixel($img1x2_in, $x*2+1, $y*2+1, imagecolorclosest($img1x2_in, $ro3, $go3, $bo3));
  }
}

//imagepng($img1x2_in, 'img1x2_in',0);

for ($x = 0; $x < W; $x++) {
  for ($y = 0; $y < H; $y++) {
    $color1 = imagecolorat($img2, $x, $y);
    $r1 = ($color1 & 0xff0000)>>16;
    $g1 = ($color1 & 0xff00)>>8;
    $b1 = ($color1 & 0xff);
    $color2 = imagecolorat($img2, $x+1, $y);
    $r2 = ($color2 & 0xff0000)>>16;
    $g2 = ($color2 & 0xff00)>>8;
    $b2 = ($color2 & 0xff);
    $color3 = imagecolorat($img2, $x, $y+1);
    $r3 = ($color3 & 0xff0000)>>16;
    $g3 = ($color3 & 0xff00)>>8;
    $b3 = ($color3 & 0xff);
    $color4 = imagecolorat($img2, $x+1, $y+1);
    $r4 = ($color4 & 0xff0000)>>16;
    $g4 = ($color4 & 0xff00)>>8;
    $b4 = ($color4 & 0xff);
    
    $ro1=($r1+$r2)/2.0;
    $go1=($g1+$g2)/2.0;
    $bo1=($b1+$b2)/2.0;
    $ro2=($r1+$r3)/2.0;
    $go2=($g1+$g3)/2.0;
    $bo2=($b1+$b3)/2.0;
    $ro3=($r1+$r2+$r3+$r4)/4.0;
    $go3=($g1+$g2+$g3+$g4)/4.0;
    $bo3=($b1+$b2+$b3+$b4)/4.0;
    
    imagesetpixel($img2x2_in, $x*2,   $y*2,   imagecolorclosest($img2x2_in, $r1, $g1, $b1));
    imagesetpixel($img2x2_in, $x*2+1, $y*2,   imagecolorclosest($img2x2_in, $ro1, $go1, $bo1));
    imagesetpixel($img2x2_in, $x*2,   $y*2+1, imagecolorclosest($img2x2_in, $ro2, $go2, $bo2));
    imagesetpixel($img2x2_in, $x*2+1, $y*2+1, imagecolorclosest($img2x2_in, $ro3, $go3, $bo3));
  }
}

//imagepng($img2x2_in, 'img2x2_in',0);
//exit;

$imgx2_out = imagecreatetruecolor(2*W, 2*H); // 2倍出力画像
$img_out = imagecreatetruecolor(W, H);       // 出力画像

$R = 56*2.0;                     // カール半径
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
  $i = ($w + $E)/$step;
  $theta = ($step * $i * M_PI)/3/($K0 + $E);
  $sin = sin($theta);
  $cos = cos($theta);
  $K = W*$cos + H*$sin; // (W/2, -H/2)をtheta回転した点のx座標＝回転系座標(u,v)におけるuの最大値

  $k = 2*$w +M_PI*$R - $K;

  $time_start = getmicrotime();

  for ($y = -H; $y < H; $y++) {
    for ($x = -W; $x < W; $x++) {
      // アルゴリズムの説明
      //   基本的に画像ドットの色を決定するわけだから、物理画像ドット
      //   から遡り論理ドットの色を決定する。
      //   (その逆に論理ドットをスキャンすると物理ドットに穴があいたりする恐れがあるため)
      
      //   物理座標→回転座標(回転)→画像座標(逆回転)
      //     上記は論理的な話であり、実際には回転→逆回転しなくても
      //     物理＝論理の点が存在する。その領域は物理座標をそのまま(要シフト)画像座標として
      //     点の色を求める。
      
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
      
      
      // 物理座標から画像座標へ変換しておく
      //    物理座標(x, y)
      //       -W <= x <= W-1
      //       -H <= y <= H-1
      //    画像座標(ix, iy)
      //        0 <= ix <= 2*W-1
      //       2*H-1 => iy >= 0
      $ix = $x + W;
      $iy = -$y + H -1;
      
      // rgbの取出し
      //
      $color = imagecolorat($img1x2_in, $ix, $iy);
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
      if (($c_mode == 0 && ($u <= $k)) || ($c_mode == 1 && ($p <= -$Q))) {
	// area0
	// 紙の影よりも左
	// ここは論理ピクセル＝物理ピクセルのため、何もしない
	
      } else if ($c_mode == 1 && ($p <= -$R)) {
	// areaX
	// カールモードでの影の領域
	$u7 = 2*$w - $u + 1.1*M_PI*$R;
	// 回転座標を逆変換
	$x7 = $u7*$cos + $v*$sin;
	$y7 = -$u7*$sin + $v*$cos;
	
	if ((-W <= $x7) && ($x7 < W) && (-H <= $y7) && ($y7 < H)) {
	  $c2 = sqrt(sqrt($alpha));
	  $r = $r * $c2;
	  $g = $g * $c2;
	  $b = $b * $c2;
	}
    } else if ($p <= 0) {
      // area1 or area4
      // 紙の重なりの部分
      if ($c_mode == 0) {
	// 非カールモード
	$u4 = 2*$w - $u + M_PI*$R;
	$coeff = 1.0;
      } else {
	// カールモード
	$u4 = M_PI * $R + $R*asin(-$p/$R) + $w;
	$coeff = $alpha + (1- $alpha)*cos(asin(-$p/$R));
      }
      // 回転座標を逆変換
      $x4 = $u4*$cos + $v*$sin;
      $y4 = -$u4*$sin + $v*$cos;
      
      if ((-W <= $x4) && ($x4 < W) && (-H <= $y4) && ($y4 < H)) {
	// area4
	if ($m_mode==2) {
	  // 裏紙
	  $r = $g = $b = URA*$coeff;
	} else if ($m_mode == 0) {
	  // 半透明
	  $ix4 = ($x4 + W);
	  $iy4 = (-$y4 + H -1);
	  
	  $color = imagecolorat($img1x2_in, $ix4, $iy4);
	  $r4 = ($color & 0xff0000)>>16;
	  $g4 = ($color & 0xff00)>>8;
	  $b4 = ($color & 0xff);
	  $r = sqrt($r * $r4)*$coeff;
	  $g = sqrt($g * $g4)*$coeff;
	  $b = sqrt($b * $b4)*$coeff;
	} else {
	  // 裏画像
	  $ix4 = ($x4 + W);
	  $iy4 = (-$y4 + H -1);
	  
	  $color = imagecolorat($img1x2_in, $ix4, $iy4);
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
      
      $u3 = M_PI * $R - $R*asin($p/$R) + $w;
      $x3 = $u3*$cos + $v*$sin;
      $y3 = -$u3*$sin + $v*$cos;
      
      $u2 = $R*asin($p/$R) + $w;
      $x2 = $u2*$cos + $v*$sin;
      $y2 = -$u2*$sin + $v*$cos;
      
      $coeff = $alpha + (1- $alpha)*cos(asin($p/$R));      
      if ((-W <= $x3) && ($x3 <= W) && (-H <= $y3) && ($y3 <= H)) {
	// area3
	if ($m_mode==2) {
	  // 裏紙
	  $r = $g = $b = URA * $coeff;
	} else if ($m_mode == 0){
	  // 半透明
	  $ix3 = ($x3 + W);
	  $iy3 = (-$y3 + H -1);
	  
	  $color = imagecolorat($img1x2_in, $ix3, $iy3);
	  $r3 = ($color & 0xff0000)>>16;
	  $g3 = ($color & 0xff00)>>8;
	  $b3 = ($color & 0xff);
	  
	  $ix2 = ($x2 + W);
	  $iy2 = (-$y2 + H -1);
	
	  $color = imagecolorat($img1x2_in, $ix2, $iy2);
	  $r2 = ($color & 0xff0000)>>16;
	  $g2 = ($color & 0xff00)>>8;
	  $b2 = ($color & 0xff);

	  $r = sqrt($r2 * $r3) * $coeff;
	  $g = sqrt($g2 * $g3) * $coeff;
	  $b = sqrt($b2 * $b3) * $coeff;
	} else {
	  // 裏画像
	  $ix3 = ($x3 + W);
	  $iy3 = (-$y3 + H -1);
	  
	  $color = imagecolorat($img1x2_in, $ix3, $iy3);
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
	
      } else if ((-W <= $x2) && ($x2 < W) && (-H <= $y2) && ($y2 < H)) {
	// area2
	// 座標が画像の範囲なら重なり部分
	$ix2 = ($x2 + W);
	$iy2 = (-$y2 + H -1);
	
	$color = imagecolorat($img1x2_in, $ix2, $iy2);
	$r2 = ($color & 0xff0000)>>16;
	$g2 = ($color & 0xff00)>>8;
	$b2 = ($color & 0xff);
	$r = $r2*$coeff;
	$g = $g2*$coeff;
	$b = $b2*$coeff;
	
      } else {
	// area5
	// そうでなければ下の部分
	$color = imagecolorat($img2x2_in, $ix, $iy);
	$r = ($color & 0xff0000)>>16;
	$g = ($color & 0xff00)>>8;
	$b = ($color & 0xff);
	
	$c2 = $alpha;
	$r *= $c2;
	$g *= $c2;
	$b *= $c2;
	
      }
    } else if ($p <= $Q) {
      // area6
      // 次画像の影までの部分
      $color = imagecolorat($img2x2_in, $ix, $iy);
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
      $color = imagecolorat($img2x2_in, $ix, $iy);
      $r = ($color & 0xff0000)>>16;
      $g = ($color & 0xff00)>>8;
      $b = ($color & 0xff);
      
    }
    imagesetpixel($imgx2_out, $ix, $iy, imagecolorclosest($img_out, $r, $g, $b));
  }
}
$filename = sprintf("NewImage-test4-%d-%d-%03d.png", $m_mode, $c_mode, $count++);

//imagepng($imgx2_out, $filename, 0);
// imgx2_outからimg_outへのダウンサンプリング

for ($x = 0; $x < 2*W; $x+=2) {
  for ($y = 0; $y < 2*H; $y+=2) {
    $color1 = imagecolorat($imgx2_out, $x, $y);
    $r1 = ($color1 & 0xff0000)>>16;
    $g1 = ($color1 & 0xff00)>>8;
    $b1 = ($color1 & 0xff);
    $color2 = imagecolorat($imgx2_out, $x+1, $y);
    $r2 = ($color2 & 0xff0000)>>16;
    $g2 = ($color2 & 0xff00)>>8;
    $b2 = ($color2 & 0xff);
    $color3 = imagecolorat($imgx2_out, $x, $y+1);
    $r3 = ($color3 & 0xff0000)>>16;
    $g3 = ($color3 & 0xff00)>>8;
    $b3 = ($color3 & 0xff);
    $color4 = imagecolorat($imgx2_out, $x+1, $y+1);
    $r4 = ($color4 & 0xff0000)>>16;
    $g4 = ($color4 & 0xff00)>>8;
    $b4 = ($color4 & 0xff);
    
    $ro=($r1+$r2+$r3+$r4)/4.0;
    $go=($g1+$g2+$g3+$g4)/4.0;
    $bo=($b1+$b2+$b3+$b4)/4.0;
    
    imagesetpixel($img_out, $x/2, $y/2, imagecolorclosest($img_out, $ro, $go, $bo));
  }
}
imagepng($img_out, $filename, 0);

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
