<?php
require_once('ContinuousDistribution.php');

class Gamma extends ContinuousDistribution {
	private $k;
	private $theta;
	
	function __construct($k = 1.0, $theta = 1.0) {
		$this->k = $k;
		$this->theta = $theta;
	}

	//These are wrapper functions that call the static implementations with what we saved.
	
	/**
		Returns a random float
		
		@return float The random variate.
	*/
	public function rvs() {
		return self::rvs($this->k, $this->theta);
	}
	
	/**
		Returns the probability distribution function
		
		@param float $x The test value
		@return float The probability
	*/
	public function pdf($x) {
		return self::pdf($x, $this->k, $this->theta);
	}
	
	/**
		Returns the cumulative distribution function, the probability of getting the test value or something below it
		
		@param float $x The test value
		@return float The probability
	*/
	public function cdf($x) {
		return self::cdf($x, $this->k, $this->theta);
	}
	
	/**
		Returns the survival function, the probability of getting the test value or something above it
		
		@param float $x The test value
		@return float The probability
	*/
	public function sf($x) {
		return self::sf($x, $this->k, $this->theta);
	}
	
	/**
		Returns the percent-point function, the inverse of the cdf
		
		@param float $x The test value
		@return float The value that gives a cdf of $x
	*/
	public function ppf($x) {
		return self::ppf($x, $this->k, $this->theta);
	}
	
	/**
		Returns the inverse survival function, the inverse of the sf
		
		@param float $x The test value
		@return float The value that gives an sf of $x
	*/
	public function isf($x) {
		return self::isf($x, $this->k, $this->theta);
	}
	
	/**
		Returns the moments of the distribution
		
		@param string $moments Which moments to compute. m for mean, v for variance, s for skew, k for kurtosis.  Default 'mv'
		@return type array A dictionary containing the first four moments of the distribution
	*/
	public function stats($moments = 'mv') {
		return self::stats($moments, $this->k, $this->theta);
	}
	
	//These represent the calculation engine of the class.
	
	/**
		Returns a random float between $minimum and $minimum plus $maximum
		
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return float The random variate.
	*/
	static function rvs($k = 1, $theta = 1) {
		$floork = floor($k);
		$fractionalk = $k - $floork;

		$sumLogUniform = 0;
		for ($index = 1; $index <= $floork; $index++) {
			$sumLogUniform += log(self::randFloat());
		}

		$m = 0;
		$xi = 0;
		$V = array(0)
		do {
			$m++;

			$V[] = self::randFloat();
			$V[] = self::randFloat();
			$V[] = self::randFloat();

			if ($V[3*$m - 2] <= M_E/(M_E + $fractionalk)) {
				$xi = pow($V[3*$m - 1], 1/$fractionalk);
				$eta = $V[3*$m]*pow($xi, $fractionalk - 1);
			}
			else {
				$xi = 1 - log($V[3*$m - 1]);
				$eta = $V[3*$m]*exp(-$xi);
			}
		} while($eta > pow($xi, $fractionalk - 1)*exp(-$xi));

		return $theta*($xi - $sumLogUniform);
	}
	
	/**
		Returns the probability distribution function
		
		@param float $x The test value
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return float The probability
	*/
	static function pdf($x, $k = 1, $theta = 1) {
		return pow($x, $k - 1)*exp(-$x/$theta)/(Stats::gamma($k)*pow($theta, $k));
	}
	
	/**
		Returns the cumulative distribution function, the probability of getting the test value or something below it
		
		@param float $x The test value
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return float The probability
	*/
	static function cdf($x, $k = 1, $theta = 1) {
		return Stats::lowerGamma($k, $x/$theta)/Stats::gamma($k);
	}
	
	/**
		Returns the survival function, the probability of getting the test value or something above it
		
		@param float $x The test value
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return float The probability
	*/
	static function sf($x, $k = 1, $theta = 1) {
		return 1.0 - self::cdf($x, $k, $theta);
	}
	
	/**
		Returns the percent-point function, the inverse of the cdf
		
		@param float $x The test value
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return float The value that gives a cdf of $x
	*/
	static function ppf($x, $k = 1, $theta = 1) {
		return 0; //TODO: Gamma PPF
	}
	
	/**
		Returns the inverse survival function, the inverse of the sf
		
		@param float $x The test value
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return float The value that gives an sf of $x
	*/
	static function isf($x, $k = 1, $theta = 1) {
		return self::ppf(1.0 - $x, $k, $theta);
	}
	
	/**
		Returns the moments of the distribution
		
		@param string $moments Which moments to compute. m for mean, v for variance, s for skew, k for kurtosis.  Default 'mv'
		@param float $k Shape parameter
		@param float $theta Scale parameter
		@return type array A dictionary containing the first four moments of the distribution
	*/
	static function stats($moments = 'mv', $k = 1, $theta = 1) {
		$moments = array();
		
		if (strpos($moments, 'm') !== FALSE) $moments['mean'] = $k*$theta;
		if (strpos($moments, 'v') !== FALSE) $moments['variance'] = $k*pow($theta, 2);
		if (strpos($moments, 's') !== FALSE) $moments['skew'] = 2/sqrt($k);
		if (strpos($moments, 'k') !== FALSE) $moments['kurtosis'] = 6/$k;
		
		return $moments;
	}
}
?>