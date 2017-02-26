
foreach ($arr as &$value){
	$mincost = +inf
	$match = null

	foreach ($arr as $vlaue){
		$f_prime = clone(f)
		$v_prime = clone(v)
		c = diversify($f_prime, $v_prime)

		if ($c < $minCost){
			$minCost = $c
			$match = $v
		}

	$P = union($P, $f, $match)
	$U = subtract($U, $match)
	}

	return $P
}