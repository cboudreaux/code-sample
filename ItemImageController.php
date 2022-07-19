<?php

namespace App\Http\Controllers\ItemImages;

use App\Http\Controllers\Controller;
use App\Models\ItemImage;
use App\Models\Item;
use App\Services\GoogleImageTextReaderService;
use App\Traits\SavePicture;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemImageController extends Controller
{
	use SavePicture;

	public function create(): Factory|View
	{
		return view('admin.item-images.upload');
	}

	public function read(Request $request, ItemImage $itemImage, GoogleImageTextReaderService $googleImageTextReaderService): \Illuminate\Contracts\View\View|Factory|\Illuminate\Contracts\Foundation\Application|RedirectResponse
	{
		$itemImage->pic_url = $this->savePic($request, 'picture', 200);

		$text = $googleImageTextReaderService->readTextFromImage($itemImage->pic_url);

		$itemImage->content = $text;
		$itemImage->save();

		return view('admin.item-images.add-metadata', ['text' => $text, 'image' => $itemImage]);
	}

	public function store(Request $request, ItemImage $itemImage): RedirectResponse
	{
		if (in_array($request->type, ['lockbox', 'lockbox_combo', 'doorlock', 'flyerbox'])) {
			$itemImage->width = null;
		} else {
			$itemImage->width = $request->width == 'na' ? null : $request->width;
		}

		$itemImage->content 			= $request->text;
		$itemImage->type 				= $request->type;
		$itemImage->pic_url 			= $request->url;
		$itemImage->broker_id 			= user()->broker->id;
		$itemImage->contains_agent_name = $request->has_agent_name;
		$itemImage->is_open_house_rider = $request->is_open_house_rider == 'on' ? 1 : 0;
		$itemImage->is_supra = $request->type == 'lockbox' ? true : false;

		$itemImage->save();

		return redirect()->route('newImage')->with('message', 'Your image is saved.')->with('alert-class', 'success');
	}

	public function edit(ItemImage $itemimage): \Illuminate\Contracts\View\View|Factory|\Illuminate\Contracts\Foundation\Application
	{
		$itemCount = Item::where('item_image_id', $itemimage->id)->count();

		return view('itemImages.edit', ['image' => $itemimage, 'itemCount' => $itemCount]);
	}

	public function update(Request $request, ItemImage $itemImage): RedirectResponse
	{
		$itemImage->type 				= $request->type;
		$itemImage->width 				= $request->width == 'na' ? null : $request->width;
		$itemImage->content 			= $request->text;
		$itemImage->is_open_house_rider = $request->is_open_house_rider == 'on' ? 1 : 0;

		$itemImage->save();

		return back()->with('message', 'The image is updated.')->with('alert-class', 'success');
	}

	public function destroy(ItemImage $itemImage): RedirectResponse
	{
		if($itemImage->items->count() > 0){
			return back()
				->with('message', 'There are ' . $itemImage->items->count() . ' items tied to that image. You must re-assign or delete those items before you can delete this image.',)
				->with('alert-class', 'danger');
		}

		$itemImage->forceDelete();

		return back()->with('message', 'The image is deleted.')->with('alert-class', 'success');
	}
}