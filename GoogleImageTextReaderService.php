<?php

namespace App\Services;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GoogleImageTextReaderService {

	/**
	 * Send the image to Google to read the text
	 *
	 * @param $path
	 * @return mixed
	 * @throws ApiException
	 * @throws ValidationException
	 */
	public function readTextFromImage($path): mixed
	{
		$imageAnnotator = new ImageAnnotatorClient([
			'credentials' => __DIR__ . '/vision_auth.json',
		]);


		$image = file_get_contents($path);
		$response = $imageAnnotator->textDetection($image);
		$texts = $response->getTextAnnotations();

		if (! empty($texts[0])) {
			$content = trim(preg_replace('/\s+/', ' ', $texts[0]->getDescription()));
		} else {
			$content = null;
		}

		$imageAnnotator->close();

		return $content;
	}
}