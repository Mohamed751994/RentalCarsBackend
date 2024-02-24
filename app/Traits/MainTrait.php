<?php

namespace App\Traits;

use App\Models\Rating;
use App\Models\Tanant;
use App\Models\Vendor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

trait MainTrait
{
    public $insertMsg = ' تم إنشاء العنصر بنجاح ';
    public $updateMsg = 'تم تحديث العنصر بنجاح';
    public $deleteMsg = 'تم حذف العنصر بنجاح';
    public $error = 'يوجد مشكلة ما';

    public $paginate = 10;



    //Success Response
    public function successResponse($message = '',$data = [],$statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    //Error Response
    public function errorResponse($message = '', $statusCode=400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $statusCode);
    }


    //Main Upload File Method
    public function uploadFile($request,$fileInputName, $moveTo)
    {
        $file = $request->file($fileInputName);
        $fileUploaded=rand(1,99999999999).'__'.$file->getClientOriginalName();
        $file->move($moveTo, $fileUploaded);
        return $fileUploaded;
    }

    //Main Upload Multiple File Method
    public function uploadMultipleFile($request,$fileInputName, $moveTo)
    {
        $multiple = [];
        foreach ($request->file($fileInputName) as $single)
        {
            $fileUploaded=rand(1,99999999999).'__'.$single->getClientOriginalName();
            $single->move($moveTo, $fileUploaded);
            array_push($multiple,$fileUploaded);
        }
        return $multiple;
    }

    //transform from implode to explode
    public function explodeData($value)
    {
        if(!$value)
        {
            return [];
        }
        else
        {
            $lists = [];
            foreach (explode(',', $value) as $item)
            {
                array_push($lists, $item);
            }
            return $lists;
        }
    }

    //Full image path in array
    public function image_full_path_for_array($value)
    {
        if(!$value)
        {
            return null;
        }
        else
        {
            $fullPath = [];
            foreach(json_decode($value) as $v)
            {
                array_push($fullPath, $this->image_full_path($v));
            }
            return $fullPath;
        }
    }

    //Full image path
    public function image_full_path($image)
    {
        return asset('/uploads/'. $image);
    }

    //Save Vendor Details when User Register
    public function save_new_vendor_details($user)
    {
        $vendorArray = [
            'name'         => $user->name,
            'user_id'      => $user->id,
            'status'       =>0
        ];
        Vendor::create($vendorArray);
    }

    //return auth user id
    public function user_id()
    {
        return (Auth::check()) ? auth()->user()->id : null;
    }

    //return total amount
    public function get_total_amount($featuresPrices, $price_in_days)
    {
        return ($featuresPrices + $price_in_days);
    }

    //return total amount after discount
    public function get_total_amount_after_discount($total_amount, $discount)
    {
        return  $total_amount - ($total_amount * $discount / 100);
    }


    //Change Status
    public function quickChangeTrait($request)
    {
        $model = $request->model;
        $id = $request->id;
        $val = $request->val;
        $col = $request->col;
        if($model == 'Vendor')
        {
            Vendor::whereId($id)->update([$col=> $val]);
        }
        return response()->json(['success'=>true]);
    }

    //Average Rating
    public function rating($id, $type)
    {
        return Rating::where(['type_id'=> $id, 'type' =>$type])->avg('rate');
    }



    //get all dates between two dates
    function get_dates_between_two_dates_for_car($carID)
    {
        $tanant_car_exists = Tanant::where('car_id',$carID)->get();
        $dates_of_car = [];
        foreach ($tanant_car_exists as $val)
        {
            $period = CarbonPeriod::create($val->from_date, $val->to_date);
            foreach ($period as $date) {
                array_push($dates_of_car, [ 'status'=>$val->status ,'date' =>$date->format('Y-m-d')]);
            }
        }
        return $dates_of_car;
    }
    // check availability
    function check_if_car_reserved_or_not($fromDate, $toDate , $carID)
    {
        $request_coming_dates = CarbonPeriod::create($fromDate, $toDate);
        foreach ($this->get_dates_between_two_dates_for_car($carID) as $status_and_date)
        {
            foreach ($request_coming_dates as $requestDate) {
                if($status_and_date['status'] == 'approved' && ($requestDate->format('Y-m-d') === $status_and_date['date']))
                {
                    return $this->errorResponse('عفواً السيارة محجوزة في الفترة الحالية يمكنك الضغط علي الأيام المتاحة قبل الحجز');
                }
            }
        }
    }



    //Sendgrid api emails
    public function sendEmail($email, $name, $body, $subject)
    {

        $headers = array(
            'Authorization: Bearer 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' ,
            'Content-Type: application/json'
        );

        $data = array(
            "personalizations" => array(
                array(
                    "to" => array(
                        array(
                            "email" => $email,
                            "name" => $name
                        ),
                    )

                )
            ),
            "from" => array(
                "email" =>"mohamed.gamal@khomrigroup.com"
            ),
            "subject" => $subject,
            "content" => array(
                array(
                    "type" => "text/html",
                    "value" => $body
                )
            )
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }








}
