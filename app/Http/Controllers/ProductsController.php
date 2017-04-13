<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Image;
use App\Product;
use App\ProductType;
use App\Vendor;
use Aws\S3\Exception\S3Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $products = Product::orderBy('name')
                ->with('type', 'vendor', 'images')
                ->get();
        }
        catch (\Exception $e) {
            # Note: this is in parent class
            $this->logError($e , 'An error occurred while loading products');

            return back()
                ->withErrors('Could not load products. Support staff have been notified.')
        }

        return view('products.list')
            ->with('products', $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $product = Product::create($request->input());

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')
                    ->store('public/images/products');

                $image = new Image([
                    'path' => $image_path
                ]);

                $product->images()
                    ->save($image);
            }

            $product->save();

        }
        catch (S3Exception $e) {
            # Note: this is in parent class
            $this->logError($e , 'An image could not be saved to S3.');
            DB::rollback();

            return back()
                ->withErrors('Image could not be uploaded. Support staff have been notified');
        }
        catch (\Exception $e) {
            # Note: this is in parent class
            $this->logError($e , 'An error occurred while saving a new product');
            DB::rollback();

            return back()
                ->withErrors('Product could not be saved. Support staff have been notified');
        }

        DB::commit();
        session()->flash('message_success', 'Product saved.');

        return redirect(route('products.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            # Note: show view does not require product vendor or type
            $product = Product::with('images')
                ->findOrFail($id);
        }
        catch (ModelNotFoundException $e) {
            return back()
                ->withErrors('Specified product could not be found.');
        }

        return view('products.show')
            ->with('product', $product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $product = Product::with('type', 'vendor')
                ->findOrFail($id);

            $types = ProductType::all()
                ->pluck('name', 'id');

            $vendors = Vendor::all()
                ->pluck('name', 'id');
        }

        catch (ModelNotFoundException $e) {
            return back()
                ->withErrors('Specified product could not be found.');
        }
        catch (\Exception $e) {
            # Note: this is in parent class
            $this->logError($e , 'An error occurred while loading product edit form');

            return back()
                ->withErrors('Could not load page. Support staff has been notified');
        }

        return view('products.edit')
            ->with('product', $product)
            ->with('types', $types)
            ->with('vendors', $vendors);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')
                    ->store('public/images/products');

                $image = new Image(['path' => $image_path]);

                $product->images()
                    ->save($image);
            }

            $product->update($request->input());
        }
        catch (ModelNotFoundException $e) {
            DB::rollback();

            return back()
                ->withErrors('Specified product could not be found');
        }
        catch (S3Exception $e) {
            # Note: this is in parent class
            $this->logError($e , 'An image could not be saved to S3.');
            DB::rollback();

            return back()
                ->withErrors('Image could not be uploaded. Support staff have been notified');
        }
        catch (\Exception $e) {
            # Note: this is in parent class
            $this->logError($e , 'An error occurred while updating product');
            DB::rollback();

            return back()
                ->withErrors('Product could not be updated. Support staff have been notified');
        }

        DB::commit();
        session()->flash('message_success', 'Product updated');

        return redirect(route('products.index'));
    }
}
