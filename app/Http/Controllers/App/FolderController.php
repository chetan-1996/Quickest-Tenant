<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\admin\LeadHistory;
use App\Models\admin\EstimateHistory;
use App\Models\admin\AttachmentHistory;

class FolderController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function showNestedDirectoriesWithFiles($id)
    {
        $nestedDirectoriesWithFiles = $this->getNestedDirectoriesWithFiles($id);

        return response()->json($nestedDirectoriesWithFiles);
    }

    public function getNestedDirectoriesWithFiles(Request $request)
    {
        $parentId = 0;
        $input = $request->all();
        $id = Crypt::decrypt($input['id']);
        $attachment_id = $input['attachment_id'];
        $directories = Folder::query()
            ->where('parent_id', $parentId)
            ->where('company_id', $this->company_id)
            ->where('lead_id', $id)
            ->where(function ($query) use ($attachment_id) {
                if ($attachment_id > 0) {
                    $query->where('id', '=', $attachment_id);
                }
            })
            ->orderBy('name')
            ->get();

        foreach ($directories as $directory) {
            $directory['files'] = File::where('folder_id', $directory->id)->get();
//            $directory['subdirectories'] = $this->getNestedDirectoriesWithFiles($directory->id);
        }

//        return $this->sendResponse(['folder'=>$directories], 'Folder Saved!');
        return $directories;
    }

    public function fileUpload(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'folder_id' => 'required',
            // 'files_name.*' => 'max:1024|mimes:jpeg,png,jpg,JPEG,PNG,JPG',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        // Check user space limit
        $totalSpaceUsed = File::select('company_id', DB::raw('SUM(file_size) as total_file_size'))
            ->where('company_id', $this->company_id)
            ->groupBy('company_id')
            ->get();

        $total_storage_size = $totalSpaceUsed->count() > 0 ? $totalSpaceUsed[0]->total_file_size : 0;

        $user_data = User::where('id', $this->company_id)->select('storage_capacity')->first();
        $folder_id = Crypt::decrypt($input['folder_id']);
        $id = 0;
        $input['lead_id'] = $folder_id;
        $input['path_name'] = $folder_id;
        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $input['parent_id'] = 0;

        // Check if folder already exists
        $existingFolder = Folder::where('name', '=', $input['name'])
            ->where('company_id', $input['company_id'])
            ->when($folder_id != 0, function ($query) use ($id,$folder_id) {
                // $query->where('id', '!=', $id);
                $query->where('lead_id', '=', $folder_id);
            })
            ->first();

        if ($existingFolder) {
            return response()->json(['success' => 'Attachment exists!'], 409);
        }

        // Check available space before entering the loop
        $fileSizes = collect($request->file('files_name'))->map->getSize()->toArray();
        $totalFileSizes = array_sum($fileSizes);

        /*if (($total_storage_size + $totalFileSizes) > $user_data->storage_capacity * 1024 * 1024) {
            return response()->json(['error' => 'Not enough space available'], 400);
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
                        'company_id' => $this->company_id,
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
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            }
        }
        return response()->json(['success' => 'Attachment Saved!'], 201);
    }

    public function folderFileUpload(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'file_folder_id' => 'required',
            // 'only_files_name.*' => 'max:1024|mimes:jpeg,png,jpg,JPEG,PNG,JPG',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        // Check user space limit
        $totalSpaceUsed = File::select('company_id', DB::raw('SUM(file_size) as total_file_size'))
            ->where('company_id', $this->company_id)
            ->groupBy('company_id')
            ->get();

        $total_storage_size = $totalSpaceUsed->count() > 0 ? $totalSpaceUsed[0]->total_file_size : 0;

        $user_data = User::where('id', $this->company_id)->select('storage_capacity')->first();
        $folder_id = Crypt::decrypt($input['file_folder_id']);
        $attachment_id = $input['file_attachment_id'];
        $id = 0;
        $input['folder_id'] = $attachment_id;
        // $input['lead_id'] = $folder_id;
        $input['path_name'] = $folder_id;
        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $input['parent_id'] = 0;


        // Check available space before entering the loop
        $fileSizes = collect($request->file('only_files_name'))->map->getSize()->toArray();
        $totalFileSizes = array_sum($fileSizes);

       /* if (($total_storage_size + $totalFileSizes) > $user_data->storage_capacity * 1024 * 1024) {
            return response()->json(['error' => 'Not enough space available'], 400);
        }*/

        if ($request->hasFile('only_files_name')) {
            foreach ($request->file('only_files_name') as $file) {

                $fileSize = $file->getSize();
                $originalFileName = $file->getClientOriginalName();

                DB::beginTransaction();

                try {
                    // Generate a unique identifier (e.g., timestamp)
                    $uniqueIdentifier = date('is');

                    // Handle file upload
                    $path = Storage::disk('s3')->put("public/{$input['company_id']}/{$input['path_name']}/{$attachment_id}/", $file, 'public');
                    $fileName = basename(Storage::disk('s3')->url($path));

                    // Generate a new file name with the original file name and unique identifier
                    $newFileName = $uniqueIdentifier. '_' .$originalFileName;

                    // Rename the file on S3
                    Storage::disk('s3')->move($path, "public/{$input['company_id']}/{$input['path_name']}/{$attachment_id}/{$newFileName}");

                    // Save file information to the database with the new file name
                    $fileCreate = File::create([
                        'path' => "public/{$input['company_id']}/{$input['path_name']}/{$attachment_id}/{$newFileName}",
                        'name' => $newFileName,
                        'folder_id' => $attachment_id,
                        'file_size' => $fileSize,
                        'user_id' => $this->logged_user->id,
                        'company_id' => $this->company_id,
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
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            }
            /*foreach ($request->file('only_files_name') as $file) {

                $fileSize = $file->getSize();
                $file_name =$file->getClientOriginalName();

                DB::beginTransaction();

                try {

                    // Handle file upload
                   $path = Storage::disk('s3')->put("public/{$input['company_id']}/{$input['path_name']}/{$attachment_id}/", $file, 'public');
                    $fileName = basename(Storage::disk('s3')->url($path));

                    // Save file information to the database
                    File::create([
                        'path' => "public/{$input['company_id']}/{$input['path_name']}/{$attachment_id}/{$file_name}",
                        'name' => $file_name,
                        'folder_id' => $attachment_id,
                        'file_size' => $fileSize,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            }*/
        }
        return response()->json(['success' => 'Attachment Saved!'], 201);
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
        if($input['is_type']==1){
            $folder = Folder::findOrFail($input['id']);

            // Call a method to recursively delete the folder and its contents
            // $this->deleteFolderAndContents($folder);

            // if ($folder->name) {
                // Storage::disk('s3')->deleteDirectory($folder->name);
            // }
            // Delete the folder record
            $folder->delete();
            // return $this->sendError('Folder Deleted!', ['error' => 'Folder Deleted!'], 400);
            return response()->json(['success' => 'Attachment Deleted!'], 201);
        }
        if($input['is_type']==0){
            $itemData = DB::table('files')->where('id', $input['id'])->first();
            //File::find($input['id']);

            if ($itemData->path && Storage::disk('s3')->exists($itemData->path)) {
                Storage::disk('s3')->delete($itemData->path);
            }

            DB::table('files')->where('id', $input['id'])->delete();

            $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $this->logged_user->email)->first();
            $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
            $leadhistory = AttachmentHistory::where('attachment_id', $input['id'])->where('company_id', $tcompany_id)->delete();

            return response()->json(['success' => 'Files Deleted!'], 201);
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

    public function downloadFile(Request $request)
    {
        $file_path = $request->input('file_path');
        $folder_id = $request->input('folder_id');
        $lead_id = $request->input('lead_id');
        $filePath = 'public/'.$this->company_id.'/'.$lead_id.'/'.$folder_id.'/'.$file_path;
        $file = Storage::disk('s3')->get($filePath);
//        $file = Storage::disk('s3')->url($filePath);

        // Determine the MIME type
        $mimeType = Storage::disk('s3')->mimeType($filePath);

        // Set the Content-Type header dynamically
        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
