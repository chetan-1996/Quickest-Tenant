<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search_arr = $request->query('search');

        $records = DB::table('products')
            ->where('company_id', $this->company_id)
            ->where(function ($query) use ($search_arr) {
                $query->orwhere('name', 'like', '%' . $search_arr . '%');
                $query->orwhere('description', 'like', '%' . $search_arr . '%');
            })
            ->select('*')->orderBy('id', 'DESC');
        $datas = $records->paginate(30);

        foreach ($datas as $record) {
            $record->image_one = Storage::url($record->image_one);
            $record->image_two = Storage::url($record->image_two);
            $record->image_three = Storage::url($record->image_three);
        }

        return response()->json($datas);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $id = $input['id'];

        if ($id == 0) {
            $validator = Validator::make($input, [
                'name' => 'required',
                'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
            ]);
        }

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        if (Product::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
            if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Product exists', ['Product exists'], 400);
        }
        if ($id == 0) {
            if ($request->h_image_one) {
                $folderPath = public_path('storage/uploads/thumbnail/');
                $image_parts = explode(";base64,", $request->h_image_one);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
                $imageName = uniqid() . '-1' . '.' . $ext;
                $imageFullPath = $folderPath . $imageName;
                file_put_contents($imageFullPath, $image_base64);
                $input['image_one'] = 'public/uploads/thumbnail/' . $imageName;
            }

            if ($request->h_image_two) {
                $folderPath = public_path('storage/uploads/thumbnail/');
                $image_parts = explode(";base64,", $request->h_image_two);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
                $imageName = uniqid() . '-2' . '.' . $ext;
                $imageFullPath = $folderPath . $imageName;
                file_put_contents($imageFullPath, $image_base64);
                $input['image_two'] = 'public/uploads/thumbnail/' . $imageName;
            }

            if ($request->h_image_three) {
                $folderPath = public_path('storage/uploads/thumbnail/');
                $image_parts = explode(";base64,", $request->h_image_three);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
                $imageName = uniqid() . '-3' . '.' . $ext;
                $imageFullPath = $folderPath . $imageName;
                file_put_contents($imageFullPath, $image_base64);
                $input['image_three'] = 'public/uploads/thumbnail/' . $imageName;
            }

            $product = Product::create($input);
        } else {
            $productData = Product::find($id);

            if ($request->hasFile('image_one')) {
                $request->validate([
                    'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                ]);

                if (Storage::exists($productData->image_one)) {
                    Storage::delete($productData->image_one);
                }

                $folderPath = public_path('storage/uploads/thumbnail/');
                $image_parts = explode(";base64,", $request->h_image_one);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
                $imageName = uniqid() . '-1' . '.' . $ext;
                $imageFullPath = $folderPath . $imageName;
                file_put_contents($imageFullPath, $image_base64);
                $input['image_one'] = 'public/uploads/thumbnail/' . $imageName;
            }

            if ($request->hasFile('image_two')) {
                $request->validate([
                    'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                ]);
                if (Storage::exists($productData->image_two)) {
                    Storage::delete($productData->image_two);
                }

                $folderPath = public_path('storage/uploads/thumbnail/');
                $image_parts = explode(";base64,", $request->h_image_two);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
                $imageName = uniqid() . '-2' . '.' . $ext;
                $imageFullPath = $folderPath . $imageName;
                file_put_contents($imageFullPath, $image_base64);
                $input['image_two'] = 'public/uploads/thumbnail/' . $imageName;
            }

            if ($request->hasFile('image_three')) {
                $request->validate([
                    'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                ]);
                if (Storage::exists($productData->image_three)) {
                    Storage::delete($productData->image_three);
                }

                $folderPath = public_path('storage/uploads/thumbnail/');
                $image_parts = explode(";base64,", $request->h_image_three);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
                $imageName = uniqid() . '-3' . '.' . $ext;
                $imageFullPath = $folderPath . $imageName;
                file_put_contents($imageFullPath, $image_base64);
                $input['image_three'] = 'public/uploads/thumbnail/' . $imageName;
            }
            $product = Product::find($id)->update($input);
        }
        return $this->sendResponse([], 'Product Saved');
    }

    public function show($id)
    {
        $product = Product::find($id)->toArray();

        if (is_null($product)) {
            return $this->sendError('Product not found!', ["Item not found!"], 400);
        }

        return $this->sendResponse($product, 'Product retrieved successfully.');
    }

    public function editStatus(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        if (!Product::where('id', $input['id'])->first()) {
            return $this->sendError('Product exists!', ["Product exists!"], 400);
        }
        $product = Product::where('id', $input['id'])->update(["status" => $input['status']]);

        return $this->sendResponse([], 'Product status updated!');
    }

    public function destroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $product = Product::where('id', $input['id'])->delete();

        return $this->sendResponse([], 'Product Deleted!');
    }
}
