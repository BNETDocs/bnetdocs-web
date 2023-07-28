<?php

namespace BNETDocs\Views\Packet;

class IndexVb extends \BNETDocs\Views\Base\Vb
{
  public static function invoke(\BNETDocs\Interfaces\Model $model): void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\Index)
    {
      throw new \BNETDocs\Exceptions\InvalidModelException($model);
    }

    $model->_responseHeaders['Content-Type'] = self::mimeType();

    echo "'\n";
    echo "'  BNETDocs, the documentation and discussion website for Blizzard protocols\n";
    echo "'  Copyright (C) 2003-" . \date('Y') . " \"Arta\", Don Cullen \"Kyro\", Carl Bennett, others\n";
    echo "'  <" . \CarlBennett\MVC\Libraries\Common::relativeUrlToAbsolute('/legal') . ">\n";
    echo "'\n";
    echo "'  BNETDocs is free software: you can redistribute it and/or modify\n";
    echo "'  it under the terms of the GNU Affero General Public License as published by\n";
    echo "'  the Free Software Foundation, either version 3 of the License, or\n";
    echo "'  (at your option) any later version.\n";
    echo "'\n";
    echo "'  BNETDocs is distributed in the hope that it will be useful,\n";
    echo "'  but WITHOUT ANY WARRANTY; without even the implied warranty of\n";
    echo "'  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n";
    echo "'  GNU Affero General Public License for more details.\n";
    echo "'\n";
    echo "'  You should have received a copy of the GNU Affero General Public License\n";
    echo "'  along with BNETDocs.  If not, see <http://www.gnu.org/licenses/>.\n";
    echo "'\n";

    echo "'  Packet ID constants for Visual Basic 6\n";
    echo "'  Generated by BNETDocs on " . $model->timestamp->format('r') . "\n";
    echo "'\n\n";

    foreach ($model->packets as $pkt)
    {
      printf("CONST %s& = %s\n", $pkt->getName(), \str_replace('0x', '&H', $pkt->getPacketId(true)));
    }
  }
}
