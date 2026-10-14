<?php
declare(strict_types=1);

namespace App;

use setasign\Fpdi\Fpdi;

/**
 * FPDF extension with Arc, Circle and Polygon helpers
 * used by the certificate renderer.
 */
final class InfinityPDF extends Fpdi
{
    /**
     * Draw an arc (partial circle / ellipse outline or fill).
     */
    public function Arc(
        float $x,
        float $y,
        float $rx,
        float $ry,
        float $startAngle,
        float $endAngle,
        string $style = 'D'
    ): void {
        $points = [];
        $step   = 3;
        if ($startAngle > $endAngle) $endAngle += 360;

        for ($a = $startAngle; $a <= $endAngle; $a += $step) {
            $rad = deg2rad($a);
            $points[] = [$x + cos($rad) * $rx, $y + sin($rad) * $ry];
        }
        if (!$points) return;

        $k  = $this->k;
        $h  = $this->h;
        $op = ($style === 'F') ? 'f' : 'S';

        $this->_out(sprintf('%.2F %.2F m', $points[0][0] * $k, ($h - $points[0][1]) * $k));
        for ($i = 1; $i < count($points); $i++) {
            $this->_out(sprintf('%.2F %.2F l', $points[$i][0] * $k, ($h - $points[$i][1]) * $k));
        }
        $this->_out($op);
    }

    /**
     * Draw a filled or outlined circle.
     */
    public function Circle(float $x, float $y, float $r, string $style = 'D'): void
    {
        $this->Arc($x, $y, $r, $r, 0, 360, $style);
    }

    /**
     * Draw a polygon from a list of [x, y] points.
     */
    public function Polygon(array $points, string $style = 'D'): void
    {
        if (count($points) < 2) return;

        $k  = $this->k;
        $h  = $this->h;
        $op = ($style === 'F') ? 'f' : 'S';

        $this->_out(sprintf('%.2F %.2F m', $points[0][0] * $k, ($h - $points[0][1]) * $k));
        for ($i = 1; $i < count($points); $i++) {
            $this->_out(sprintf('%.2F %.2F l', $points[$i][0] * $k, ($h - $points[$i][1]) * $k));
        }
        $this->_out($op);
    }
}