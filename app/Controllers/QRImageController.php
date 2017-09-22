<?php

namespace Elbo\Controllers;

use BaconQrCode\{Renderer\Image\Png, Writer};
use Elbo\{Library\Controller, Models\ShortURL};
use Symfony\Component\{Filesystem\Filesystem, HttpFoundation\Request, HttpFoundation\Response};

class QRImageController extends Controller {
	public function run(Request $request, array &$data) {
		$url = ShortURL::where('shorturl', $data['shorturl'])->select('url')->first();

		if ($url === null) {
			return new Response('Invalid URL.', 400);
		}

		$size = (int)$request->query->get('size', 300);

		if ($size > 1000 || $size % 100 !== 0) {
			return new Response('Invalid dimensions.', 400);
		}

		$hash = hash('sha256', $data['shorturl']);
		$suffix = substr($hash, 0, 2);
		$dir = __DIR__.'/../../data/tmp/qr/'.$suffix;
		$filename = substr($hash, 2).'_'.$size.'.png';
		$file = $dir.'/'.$filename;

		$fs = $this->container->get(Filesystem::class);

		if (!$fs->exists($file)) {
			if (!$fs->exists($dir)) {
				$fs->mkdir($dir, 0777, true);
			}

			$writer = $this->container->get(Writer::class);
			$writer->getRenderer()->setHeight($size)->setWidth($size)->setMargin(1);

			$host = $request->headers->get('Host');
			$protocol = $request->headers->get('Https') ? 'https' : 'http';

			$writer->writeFile("${protocol}://${host}/${data['shorturl']}", $file);
		}

		$headers = [
			'Content-type' => 'image/png'
		];

		if ($request->query->get('download') !== null) {
			$headers['Content-Disposition'] = 'attachment; filename="qr.png"';
		}

		if (substr($_SERVER['SERVER_SOFTWARE'], 0, 5) === 'nginx') {
			$headers['X-Accel-Redirect'] = "/~qr/files/${suffix}/${filename}";
			$content = '';
		}
		else {
			$content = file_get_contents($file);
		}

		return new Response($content, 200, $headers);
	}
}
