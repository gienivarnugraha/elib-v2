<?php

namespace App\Models;

use App\Models\Order;
use setasign\Fpdi\Fpdi;
use App\Core\Contracts\Presentable;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Date\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Media extends SpatieMedia implements Presentable
{
  protected $appends = [];

  public function order()
  {
    return $this->hasMany(Order::class);
  }


  public function displayName(): Attribute
  {
    return Attribute::get(fn () => $this->name);
  }

  public function path(): Attribute
  {
    return Attribute::get(fn () => "/pdf/{$this->uuid}");
  }

  public function validate($passcode)
  {
    $today = Carbon::today()->toDateString();

    $query = $this->order()->where('user_id', '=', Auth::id());

    if ($query->exists()) {
      $query->where('is_confirmed', '=', 1);

      // check if user has and order
      if ($query->exists()) {
        // check order has matched passcode
        $query->where('passcode', '=',  $passcode);

        if ($query->exists()) {
          //check if the order still valid
          $query
            ->whereDate('date_from', '<=', $today)
            ->whereDate('date_to', '>=', $today);

          if ($query->exists()) {
            return $query->latest()->firstOrFail();
          } else {
            throw new \Exception('Order is no longer valid');
          }
        } else {
          throw new \Exception('Wrong Passcode');
        }
      } else {
        throw new \Exception('Order is not accepted');
      }
    } else {
      throw new \Exception('Order not found');
    }
  }

  public function addWatermark()
  {
    $url = $this->getPath();
    $user = Auth::user();

    // initiate FPDI
    $pdf = new Fpdi();

    $pageCount = $pdf->setSourceFile($url);

    $svg = QrCode::format('eps')->generate('asd', storage_path('qrcode.svg'));

    // iterate through all pages
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
      // import a page
      $templateId = $pdf->importPage($pageNo);

      $pdf->AddPage();
      // use the imported page and adjust the page size
      $pdf->useTemplate($templateId, ['adjustPageSize' => true]);

      $pdf->SetFont('Helvetica', '', 7.5);
      $pdf->SetAutoPageBreak(false, 0);
      $pdf->SetTextColor(255, 0, 0);

      $pdf->SetY(-10);

      //$pdf->Write(5, "This document is ordered by: {$user->name} : {$user->nik}");
    }

    $pdf->Output();



    return $pdf;
  }


  function ImageEps($file, $x, $y, $w = 0, $h = 0, $link = '', $useBoundingBox = true)
  {

    $data = file_get_contents($file);
    if ($data === false) $this->Error('EPS file not found: ' . $file);

    $regs = array();

    # EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
    preg_match('/%%Creator:([^\r\n]+)/', $data, $regs); # find Creator
    if (count($regs) > 1) {
      $version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
      if (strpos($version_str, 'Adobe Illustrator') !== false) {
        $a = explode(' ', $version_str);
        $version = (float)array_pop($a);
        if ($version >= 9)
          $this->Error('File was saved with wrong Illustrator version: ' . $file);
        #return false; # wrong version, only 1.x, 3.x or 8.x are supported
      } #else
      #$this->Error('EPS wasn\'t created with Illustrator: '.$file);
    }

    # strip binary bytes in front of PS-header
    $start = strpos($data, '%!PS-Adobe');
    if ($start > 0) $data = substr($data, $start);

    # find BoundingBox params
    preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
    if (count($regs) > 1) {
      list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
    } else $this->Error('No BoundingBox found in EPS file: ' . $file);

    $start = strpos($data, '%%EndSetup');
    if ($start === false) $start = strpos($data, '%%EndProlog');
    if ($start === false) $start = strpos($data, '%%BoundingBox');

    $data = substr($data, $start);

    $end = strpos($data, '%%PageTrailer');
    if ($end === false) $end = strpos($data, 'showpage');
    if ($end) $data = substr($data, 0, $end);

    # save the current graphic state
    $this->_out('q');

    $k = $this->k;

    if ($useBoundingBox) {
      $dx = $x * $k - $x1;
      $dy = $y * $k - $y1;
    } else {
      $dx = $x * $k;
      $dy = $y * $k;
    }

    # translate
    $this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - 2 * $y * $k - ($y2 - $y1))));

    if ($w > 0) {
      $scale_x = $w / (($x2 - $x1) / $k);
      if ($h > 0) {
        $scale_y = $h / (($y2 - $y1) / $k);
      } else {
        $scale_y = $scale_x;
        $h = ($y2 - $y1) / $k * $scale_y;
      }
    } else {
      if ($h > 0) {
        $scale_y = $h / (($y2 - $y1) / $k);
        $scale_x = $scale_y;
        $w = ($x2 - $x1) / $k * $scale_x;
      } else {
        $w = ($x2 - $x1) / $k;
        $h = ($y2 - $y1) / $k;
      }
    }

    # scale
    if (isset($scale_x))
      $this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));

    # handle pc/unix/mac line endings
    $lines = preg_split("/\r\n|[\r\n]/", $data);

    $u = 0;
    $cnt = count($lines);
    for ($i = 0; $i < $cnt; $i++) {
      $line = $lines[$i];
      if ($line == '' || $line[0] == '%') continue;

      $len = strlen($line);

      $chunks = explode(' ', $line);
      $cmd = array_pop($chunks);

      # RGB
      if ($cmd == 'Xa' || $cmd == 'XA') {
        $b = array_pop($chunks);
        $g = array_pop($chunks);
        $r = array_pop($chunks);
        $this->_out("$r $g $b " . ($cmd == 'Xa' ? 'rg' : 'RG')); #substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
        continue;
      }

      switch ($cmd) {
        case 'm':
        case 'l':
        case 'v':
        case 'y':
        case 'c':

        case 'k':
        case 'K':
        case 'g':
        case 'G':

        case 's':
        case 'S':

        case 'J':
        case 'j':
        case 'w':
        case 'M':
        case 'd':

        case 'n':
        case 'v':
          $this->_out($line);
          break;

        case 'x': # custom fill color
          list($c, $m, $y, $k) = $chunks;
          $this->_out("$c $m $y $k k");
          break;

        case 'X': # custom stroke color
          list($c, $m, $y, $k) = $chunks;
          $this->_out("$c $m $y $k K");
          break;

        case 'Y':
        case 'N':
        case 'V':
        case 'L':
        case 'C':
          $line[$len - 1] = strtolower($cmd);
          $this->_out($line);
          break;

        case 'b':
        case 'B':
          $this->_out($cmd . '*');
          break;

        case 'f':
        case 'F':
          if ($u > 0) {
            $isU = false;
            $max = min($i + 5, $cnt);
            for ($j = $i + 1; $j < $max; $j++)
              $isU = ($isU || ($lines[$j] == 'U' || $lines[$j] == '*U'));
            if ($isU) $this->_out("f*");
          } else
            $this->_out("f*");
          break;

        case '*u':
          $u++;
          break;

        case '*U':
          $u--;
          break;

          #default: echo "$cmd<br>"; #just for debugging
      }
    }

    # restore previous graphic state
    $this->_out('Q');
    if ($link)
      $this->Link($x, $y, $w, $h, $link);

    return true;
  }
}
