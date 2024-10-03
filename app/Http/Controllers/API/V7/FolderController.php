<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use LogActivity;

class FolderController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $id = 0;
        if (Folder::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where('parent_id', $input['parent_id'])->select('id')->where(function ($query) use ($id) {
            if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Folder Exists!', ['error' => 'Folder Exists!'], 400);
        }
        if ($id == 0) {
            $activityLogMsg = 'Folder created by ' . $this->logged_user->name;
            $folder = Folder::create($input);
        } else {
            $folder = Folder::find($id)->update($input);
            $activityLogMsg = 'Folder updated by ' . $this->logged_user->name;
        }

        // Add activity logs
        LogActivity::addToLog($activityLogMsg, $input);

        return $this->sendResponse([], 'Folder Saved!');
    }

    public function showNestedDirectoriesWithFiles()
    {
        $nestedDirectoriesWithFiles = $this->getNestedDirectoriesWithFiles();

        return response()->json($nestedDirectoriesWithFiles);
    }

    public function getNestedDirectoriesWithFiles($lead_id,$attachment_id=0)
    {
        // $directories = Folder::where('parent_id', $parentId)->where('company_id', $this->company_id)->get();
        $parentId = 0;
        $id = $lead_id;
        $directories = Folder::query()
            ->where('parent_id', $parentId)
            ->where('company_id', $this->company_id)
            ->where('lead_id', $lead_id)
            ->where(function ($query) use ($attachment_id) {
                if ($attachment_id > 0) {
                    $query->where('id', '=', $attachment_id);
                }
            })
            ->orderBy('name')
            ->get();

        foreach ($directories as $directory) {
            $directory['files'] = File::where('folder_id', $directory->id)->get();
            // $directory['subdirectories'] = $this->getNestedDirectoriesWithFiles($directory->id);
            $directory['subdirectories'] = [];
        }

        // return $this->sendResponse(['folder'=>$directories], 'Folder Saved!');
        return $directories;
    }

    public function destroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'is_type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }
        if($input['is_type']){
            $folder = Folder::findOrFail($input['id']);

            // Call a method to recursively delete the folder and its contents
            // $this->deleteFolderAndContents($folder);

            // if ($folder->name) {
                // Storage::disk('s3')->deleteDirectory($folder->name);
            // }
            // Delete the folder record
            $folder->delete();
            // return $this->sendError('Folder Deleted!', ['error' => 'Folder Deleted!'], 400);
            return $this->sendResponse([], 'Folder Saved!');
        }
        if(!$input['is_type']){
            $itemData = File::find($input['id']);

            if ($itemData->path) {
                Storage::disk('s3')->delete($itemData->path);
            }

            DB::table('files')->where('id', $input['id'])->delete();
            $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $this->logged_user->email)->first();
            $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
            $leadhistory = AttachmentHistory::where('attachment_id', $input['id'])->where('company_id', $tcompany_id)->delete();

            // return $this->sendError('Files Deleted!', ['error' => 'Files Deleted!'], 400);
            return $this->sendResponse([], 'Files Saved!');
        }
    }

    // Recursive method to delete a folder and its contents
    private function deleteFolderAndContents($folder)
    {
        // Delete associated files
//        $folder->files()->delete();

        // Get subdirectories and delete them recursively
        $subdirectories = $folder->subdirectories;
        foreach ($subdirectories as $subdirectory) {
            $this->deleteFolderAndContents($subdirectory);
        }

        // Delete the current folder from the S3 bucket
      /*  if ($folder->name) {
            Storage::disk('s3')->deleteDirectory($folder->name);
        }*/

        // Delete the current folder
//        $folder->delete();
    }


//    public function fileUpload(Request $request)
//    {
//        $input = $request->all();
//        $validator = Validator::make($input, [
////            'files_name' => 'max:1024', //image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|
//            'folder_id' => 'required',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
//        }
//
//        // Check user space limit
//        $totalSpaceUsed = File::select('company_id', \DB::raw('SUM(file_size) as total_file_size'))
//            ->where('company_id', $this->company_id)
//            ->groupBy('company_id')
//            ->get();
//        $total_storage_size = 0;
//        if($totalSpaceUsed->count() > 0){
//            $total_storage_size = $totalSpaceUsed[0]->total_file_size;
//        }
//
//        $user_data = User::where('id', $this->company_id)->select('storage_capacity')->first();
//        $fileSize = $request->file('files_name')->getSize();
//
//        if (($total_storage_size + $fileSize) > $user_data->storage_capacity * 1024 * 1024) {
//            return $this->sendError('Not enough space available.', ['error' => 'Not enough space available.'], 400);
//        }
//
//        $id = $input['id'];
//        $input['user_id'] = $this->logged_user->id;
//        $input['company_id'] = $this->company_id;
//        $input['parent_id'] = 0;
//
//        if (Folder::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->select('id')->where(function ($query) use ($id) {
//            if ($id != 0) {
//                $query->Where(function ($query) use ($id) {
//                    $query->where('id', '!=', $id);
//                });
//            }
//        })->first()) {
//            return $this->sendError('Folder Exists!', ['error' => 'Folder Exists!'], 400);
//        }
//        if ($id == 0) {
//            $activityLogMsg = 'Folder created by ' . $this->logged_user->name;
//            $folder = Folder::create($input);
//            $id = $folder->id;
//        } else {
//            $folder = Folder::find($id)->update($input);
//            $activityLogMsg = 'Folder updated by ' . $this->logged_user->name;
//        }
//
//        $fileArr = array();
//        if ($request->hasFile('files_name')) {
//            /* if ($id > 0) {
//                 $itemData = Item::find($id);
//                 if ($itemData->files_name) {
//                     Storage::disk('s3')->delete($itemData->files_name);
//                 }
//             }*/
//
//            $path = Storage::disk('s3')->put('public/'.$input['company_id'].'/'.$input['path_name'].'/'.$input['name'].'/', $request->files_name,'public');
//            $fileName = basename(Storage::disk('s3')->url($path));
//            $fileArr['path'] = 'public/'.$input['company_id'].'/'.$input['path_name'].'/'.$input['name'].'/'.$fileName;
//            $fileArr['name'] =$fileName;
//            $fileArr['folder_id'] =$id;
//            $fileArr['file_size'] =$request->files_name->getSize();
//        }
//        File::create($fileArr);
//        return $this->sendResponse([], 'Folder Saved!');
//    }

//    public function fileUpload(Request $request)
//    {
//        $input = $request->all();
//
//        $validator = Validator::make($input, [
//            'folder_id' => 'required',
//            'files_name.*' => 'max:1024|mimes:jpeg,png,jpg,JPEG,PNG,JPG',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
//        }
//
//        // Check user space limit
//        $totalSpaceUsed = File::select('company_id', DB::raw('SUM(file_size) as total_file_size'))
//            ->where('company_id', $this->company_id)
//            ->groupBy('company_id')
//            ->get();
//
//        $total_storage_size = $totalSpaceUsed->count() > 0 ? $totalSpaceUsed[0]->total_file_size : 0;
//
//        $user_data = User::where('id', $this->company_id)->select('storage_capacity')->first();
//
//        if ($request->hasFile('files_name')) {
//            foreach ($request->file('files_name') as $file) {
//                $fileSize = $file->getSize();
//
//                if (($total_storage_size + $fileSize) > $user_data->storage_capacity * 1024 * 1024) {
//                    return $this->sendError('Not enough space available.', ['error' => 'Not enough space available.'], 400);
//                }
//
//                DB::beginTransaction();
//
//                try {
//                    $id = $input['id'];
//                    $input['user_id'] = $this->logged_user->id;
//                    $input['company_id'] = $this->company_id;
//                    $input['parent_id'] = 0;
//
//                    // Check if folder already exists
//                    $existingFolder = Folder::where('name', '=', $input['name'])
//                        ->where('company_id', $input['company_id'])
//                        ->when($id != 0, function ($query) use ($id) {
//                            $query->where('id', '!=', $id);
//                        })
//                        ->first();
//
//                    if ($existingFolder) {
//                        return $this->sendError('Folder Exists!', ['error' => 'Folder Exists!'], 400);
//                    }
//
//                    // Use updateOrCreate for folder
//                    $folder = Folder::updateOrCreate(['id' => $id], $input);
//                    $id = $folder->id;
//
//                    // Handle file upload
//                    $path = Storage::disk('s3')->put("public/{$input['company_id']}/{$input['path_name']}/{$input['name']}/", $file, 'public');
//                    $fileName = basename(Storage::disk('s3')->url($path));
//
//                    // Save file information to the database
//                    File::create([
//                        'path' => "public/{$input['company_id']}/{$input['path_name']}/{$input['name']}/{$fileName}",
//                        'name' => $fileName,
//                        'folder_id' => $id,
//                        'file_size' => $fileSize,
//                    ]);
//
//                    DB::commit();
//                } catch (\Exception $e) {
//                    DB::rollBack();
//                    return $this->sendError('Error occurred', ['error' => $e->getMessage()], 500);
//                }
//            }
//        }
//
//        return $this->sendResponse([], 'Folder Saved!');
//    }


    public function fileUpload(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'folder_id' => 'required',
//            'files_name.*' => 'max:1024|mimes:jpeg,png,jpg,JPEG,PNG,JPG',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        // Check user space limit
        $totalSpaceUsed = File::select('company_id', DB::raw('SUM(file_size) as total_file_size'))
            ->where('company_id', $this->company_id)
            ->groupBy('company_id')
            ->get();

        $total_storage_size = $totalSpaceUsed->count() > 0 ? $totalSpaceUsed[0]->total_file_size : 0;

        $user_data = User::where('id', $this->company_id)->select('storage_capacity')->first();

        $id = $input['id'];
        $folder_id = $input['path_name'];
        $input['lead_id'] = $input['path_name'];
        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $input['parent_id'] = 0;

        // Check if folder already exists
        $existingFolder = Folder::where('name', '=', $input['name'])
            ->where('company_id', $input['company_id'])
            ->when($folder_id != 0, function ($query) use ($folder_id) {
//                $query->where('id', '!=', $folder_id);
                $query->where('lead_id', '=', $folder_id);
            })
            ->first();

        if ($existingFolder) {
            return $this->sendError('Attechment Exists!', ['error' => 'Attechment Exists!'], 200);
        }

        // Check available space before entering the loop
//        $fileSizes = collect($request->file('files_name'))->map->getSize()->toArray();
        $fileSizes = collect($request->file('files_name'))->map(function ($file) {
            // Check if $file is an instance of UploadedFile
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                return $file->getSize();
            }

            return 0; // or any default size for non-UploadedFile instances
        })->toArray();
        $totalFileSizes = array_sum($fileSizes);

        /*if (($total_storage_size + $totalFileSizes) > $user_data->storage_capacity * 1024 * 1024) {
            return $this->sendError('Not enough space available.', ['error' => 'Not enough space available.'], 200);
        }*/

        if ($request->hasFile('files_name')) {
            foreach ($request->file('files_name') as $file) {
                $fileSize = $file->getSize();
                $originalFileName = $file->getClientOriginalName();

                DB::beginTransaction();

                try {
                    // Use updateOrCreate for folder
                    $folder = Folder::updateOrCreate(['id' => $id], $input);
                    $id = $folder->id;

                    // Generate a unique identifier (e.g., timestamp)
                    $uniqueIdentifier = date('is');

                    // Handle file upload
                    $path = Storage::disk('s3')->put("public/{$input['company_id']}/{$input['path_name']}/{$id}/", $file, 'public');
                    $fileName = basename(Storage::disk('s3')->url($path));

                    // Generate a new file name with the original file name and unique identifier
                    $newFileName = $uniqueIdentifier. '_' .$originalFileName;

                    // Rename the file on S3
                    Storage::disk('s3')->move($path, "public/{$input['company_id']}/{$input['path_name']}/{$id}/{$newFileName}");

                    // Save file information to the database
                    $fileCreate = File::create([
                        'path' => "public/{$input['company_id']}/{$input['path_name']}/{$id}/{$newFileName}",
                        'name' => $newFileName,
                        'folder_id' => $id,
                        'file_size' => $fileSize,
                        'user_id' => $this->logged_user->id,
                        'company_id' => $this->company_id
                    ]);

                    DB::commit();

                    $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $this->logged_user->email)->first();
                    $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
                    $hdata['attachment_id'] = $fileCreate->id;
                    $hdata['user_id'] = $this->logged_user->id;
                    $hdata['company_id'] = $tcompany_id;
                    $hdata['email'] = $this->logged_user->email;
                    $hdata['domain'] = $this->logged_user->domain;
                    $hdata['storage_size'] = $fileSize;
                    $leadhistory = AttachmentHistory::create($hdata);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $this->sendError('Error occurred', ['error' => $e->getMessage()], 500);
                }
            }
        }

        return $this->sendResponse([], 'Attechment Saved!');
    }

    public function folderFileUpload(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'folder_id' => 'required',
//            'files_name.*' => 'max:1024|mimes:jpeg,png,jpg,JPEG,PNG,JPG',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        // Check user space limit
        $totalSpaceUsed = File::select('company_id', DB::raw('SUM(file_size) as total_file_size'))
            ->where('company_id', $this->company_id)
            ->groupBy('company_id')
            ->get();

        $total_storage_size = $totalSpaceUsed->count() > 0 ? $totalSpaceUsed[0]->total_file_size : 0;

        $user_data = User::where('id', $this->company_id)->select('storage_capacity')->first();

        $id = $input['id'];
        $folder_id = $input['path_name'];
        $input['lead_id'] = $input['path_name'];
        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $input['parent_id'] = 0;

        // Check available space before entering the loop
//        $fileSizes = collect($request->file('files_name'))->map->getSize()->toArray();
        $fileSizes = collect($request->file('files_name'))->map(function ($file) {
            // Check if $file is an instance of UploadedFile
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                return $file->getSize();
            }

            return 0; // or any default size for non-UploadedFile instances
        })->toArray();
        $totalFileSizes = array_sum($fileSizes);

        if (($total_storage_size + $totalFileSizes) > $user_data->storage_capacity * 1024 * 1024) {
            return $this->sendError('Not enough space available.', ['error' => 'Not enough space available.'], 200);
        }

        if ($request->hasFile('files_name')) {
            foreach ($request->file('files_name') as $file) {
                $fileSize = $file->getSize();
                $originalFileName = $file->getClientOriginalName();

                DB::beginTransaction();

                try {
                    // Handle file upload
                    $path = Storage::disk('s3')->put("public/{$input['company_id']}/{$input['path_name']}/{$id}/", $file, 'public');
                    $fileName = basename(Storage::disk('s3')->url($path));

                    // Generate a unique identifier (e.g., timestamp)
                    $uniqueIdentifier = date('is');

                    // Generate a new file name with the original file name and unique identifier
                    $newFileName = $uniqueIdentifier. '_' .$originalFileName;

                    // Rename the file on S3
                    Storage::disk('s3')->move($path, "public/{$input['company_id']}/{$input['path_name']}/{$id}/{$newFileName}");

                    // Save file information to the database
                    $fileCreate = File::create([
                        'path' => "public/{$input['company_id']}/{$input['path_name']}/{$id}/{$newFileName}",
                        'name' => $newFileName,
                        'folder_id' => $id,
                        'file_size' => $fileSize,
                    ]);
                    DB::commit();

                    $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $this->logged_user->email)->first();
                    $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
                    $hdata['attachment_id'] = $fileCreate->id;
                    $hdata['user_id'] = $this->logged_user->id;
                    $hdata['company_id'] = $tcompany_id;
                    $hdata['email'] = $this->logged_user->email;
                    $hdata['domain'] = $this->logged_user->domain;
                    $hdata['storage_size'] = $fileSize;
                    $leadhistory = AttachmentHistory::create($hdata);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $this->sendError('Error occurred', ['error' => $e->getMessage()], 500);
                }
            }
        }

        return $this->sendResponse([], 'Attechment Saved!');
    }

}
