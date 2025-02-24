
<?php
/** A time-series forcasting, given a series of data (w/ seasonal data), predict future data
* Src: http://home.ubalt.edu/ntsbarsh/stat-data/Forecast.htm & BUS264 (Oliver Yu's coursenote)
* @author Son Nguyen
* @since 10/1/2008
* @package Framework
* @subpackage Math
* http://adspeed.org/?p=20
*/
class CMathForecasting {
   private $mPastDatas; // array of data (eg: x1,x2,x3,x4...)
   private $mNumSeasons; // the number of seasons to consider (eg: 4 quarters/year, 7 days/week, 24 hours/day)
   /** constructor */
   function __construct($pPastDatas,$pNumSeasons) {
      $this->mPastDatas = $pPastDatas;
      $this->mNumSeasons = $pNumSeasons;
   }
   /** compute the n-season moving average */
   function computeSMA() {
      $vSMA = array();
      for ($i=0;$i<count($this->mPastDatas);$i++) {
         if ($i+$this->mNumSeasons-1>=count($this->mPastDatas)) { // out of bound, done
            break;
         } // fi
         $vSum = 0;
         for ($j=0;$j<$this->mNumSeasons;$j++) {
            $vSum += $this->mPastDatas[$i+$j];
         } // rof
         $vSMA[] = $vSum/$this->mNumSeasons;
      } // rof
      return $vSMA;
   }
   /** compute centered moving average from the n-season moving average */
   function computeCMA($pSMA) {
      $vCMA = array();
      for ($i=0;$i<count($pSMA);$i++) {
         if ($i+1>=count($pSMA)) { // out of bound, done
            break;
         } // fi
         $vCMA[] = ($pSMA[$i]+$pSMA[$i+1])/2;
      } // rof
      return $vCMA;
   }
   /** season irregularity */
   function computeNoises($pCMA) {
      $vNoises = array();
      for ($i=0;$i<count($pCMA);$i++) {
         $vStarting = floor($this->mNumSeasons/2);
         $vNoises[] = $this->mPastDatas[$i+$vStarting]/$pCMA[$i];
      } // rof
      return $vNoises;
   }
   /** comment */
   function computeSeasonIndices($pNoises) {
      $vIndices = array();
      for ($i=0;$i<$this->mNumSeasons;$i++) {
         $vSum = array();
         for ($j=$i;$j<count($pNoises);$j+=$this->mNumSeasons) {
            $vSum[] = $pNoises[$j];
         } // rof
         $vStarting = (floor($this->mNumSeasons/2)+$i)%$this->mNumSeasons;
         $vIndices[$vStarting] = array_sum($vSum)/count($vSum);
      } // rof
      ksort($vIndices);
      // also adjust these season indices
      $vSum = array_sum($vIndices);
      for ($i=0;$i<count($vIndices);$i++) {
         $vIndices[$i] = $vIndices[$i]*$this->mNumSeasons/$vSum;
      } // rof
      return $vIndices;
   }
   /** comment */
   function computeDeSeasonalized($pSeasonIndex) {
      $vDatas = array();
      for ($i=0;$i<count($this->mPastDatas);$i++) {
         $vDatas[] = $this->mPastDatas[$i]/$pSeasonIndex[$i%$this->mNumSeasons];
      } // rof
      return $vDatas;
   }
   

   /** how many future periods to predict */
   function predict($pNumFuturePeriods) {
      $vSMA = $this->computeSMA();
      if ($this->mNumSeasons%2==0) { // even
         $vCMA = $this->computeCMA($vSMA);
      } else { // odd, nSMA=CMA
         $vCMA = $vSMA;
      } // fi
      $vNoises = $this->computeNoises($vCMA);
      $vIndices = $this->computeSeasonIndices($vNoises);
      $vDeSeasonalized = $this->computeDeSeasonalized($vIndices);
      // perform regression to get the trend line
      $vRegression = new CRegressionLinear($vDeSeasonalized);
      list($vXVar,$vIntercept) = $vRegression->calculate();
      $vForecast = array();
      for ($i=0;$i<$pNumFuturePeriods;$i++) {
         $vForecast[] = $vIntercept + $vXVar*(count($this->mPastDatas)+$i);
      } // rof
      // have to re-seasonalized these values
      for ($i=0;$i<count($vForecast);$i++) {
         $vForecast[$i] = $vForecast[$i]*$vIndices[(count($this->mPastDatas)+$i)%$this->mNumSeasons];
      } // rof
      return $vForecast;
   }
}

/** perform regression analysis on the input data, make the trend line y=ax+b
* @author Son Nguyen
* @since 11/18/2005
* @package Framework
* @subpackage Math
* http://blog.trungson.com/?p=42
*/
class CRegressionLinear {
  private $mDatas; // input data, array of (x1,y1);(x2,y2);... pairs, or could just be a time-series (x1,x2,x3,...)
  /** constructor */
  function __construct($pDatas) {
    $this->mDatas = $pDatas;
  }
  /** compute the coeff, equation source: http://people.hofstra.edu/faculty/Stefan_Waner/RealWorld/calctopic1/regression.html */
  function calculate() {
    $n = count($this->mDatas);
    $vSumXX = $vSumXY = $vSumX = $vSumY = 0;
    //var_dump($this->mDatas);
    $vCnt = 0; // for time-series, start at t=0
 foreach ($this->mDatas AS $vOne) {
      if (is_array($vOne)) { // x,y pair
        list($x,$y) = $vOne;
      } else { // time-series
        $x = $vCnt; $y = $vOne;
      } // fi
      $vSumXY += $x*$y;
      $vSumXX += $x*$x;
      $vSumX += $x;
      $vSumY += $y;
      $vCnt++;
 } // rof
	$vTop=($n*$vSumXY-$vSumX*$vSumY);
	$vBottom=($n*$vSumXX-$vSumX*$vSumX);
    $a = $vBottom!=0?$vTop/$vBottom:0;
	$b=($vSumY-$a*$vSumX)/$n;

//var_dump($a,$b);

return array($a,$b);
  }
  
  /** given x, return the prediction y */
  function predict($x) {
  
    list($a,$b) = $this->calculate();
    $y=$a*$x+$b;
    return $y;
  }
}
?>




<?

// // sales data for the last 30 quarters
// $vSales = array(
 // 637381,700986,641305,660285,604474,565316,598734,688690,723406,697358,
 // 669910,605636,526655,555165,625800,579405,588317,634433,628443,570597,
 // 662584,763516,742150,703209,669883,586504,489240,648875,692212,586509
// );
// $vRegression = new CRegressionLinear($vSales);
// $vNextQuarter = $vRegression->predict(2); // return the forecast for next period

// echo $vNextQuarter;
//die;
?>


<?php

$vSales = array(
   637381,700986,641305,660285,604474,565316,598734,688690,723406,697358,
   669910,605636,526655,555165,625800,579405,588317,634433,628443,570597,
   662584,763516,742150,703209,669883,586504,489240,648875,692212,586509
);
$vForecast = new CMathForecasting($vSales,7); // sales pattern is weekly
$vResult = $vForecast->predict(7); // predict the next 7 days
var_dump($vResult);
die;
// Example #2: quarterly demand number (x1000 units)
$vSales = array(
   3,9,6,2,
   4,11,8,3,
   5,15,11,3
);
$vForecast = new CMathForecasting($vSales,4); // sales pattern is quarterly
$vResult = $vForecast->predict(4); // predict the next 4 quarters
var_dump($vResult);
?>
