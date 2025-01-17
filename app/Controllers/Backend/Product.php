<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\M_Product;
use App\Models\M_Principal;
use App\Models\M_Category;
use App\Models\M_Uom;
use App\Models\M_ProductCategory;

use Config\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Html2Text\Html2Text;

class Product extends BaseController
{
	protected $table = 'md_product';
	protected $path_folder = 'custom/image/product/';

	public function index()
	{
		$principal = new M_Principal();
		$uom = new M_Uom();

		$data = [
			'principal'	=> $principal->where('isactive', 'Y')
				->orderBy('name', 'ASC')
				->findAll(),
			'uom'	=> $uom->where('isactive', 'Y')
				->orderBy('name', 'ASC')
				->findAll()
		];

		return $this->template->render('backend/product/v_product', $data);
	}

	public function showAll()
	{
		$request = Services::request();
		$product = new M_Product($request);

		if ($request->getMethod(true) === 'POST') {
			$lists = $product->getDatatables();
			$data = [];
			$number = $request->getPost('start');

			foreach ($lists as $value) :
				$row = [];
				$ID = $value->md_product_id;

				$number++;

				$row[] = $ID;
				$row[] = $number;
				$row[] = $value->code;
				$row[] = $value->name;
				$row[] = $this->picture->render($this->path_folder, $value->image);
				$row[] = $value->m_product_id;
				$row[] = $value->principal;
				$row[] = $value->md_category1;
				$row[] = $value->md_category2;
				$row[] = $value->md_category3;
				$row[] = $value->color;
				$row[] = $value->weight;
				$row[] = $value->width;
				$row[] = $value->height;
				$row[] = $value->depth;
				$row[] = $value->volume;
				$row[] = active($value->visible);
				$row[] = active($value->isactive);
				$row[] = $this->template->table_button($ID);
				$data[] = $row;
			endforeach;

			$result = [
				'draw' => $request->getPost('draw'),
				'recordsTotal' => $product->countAll(),
				'recordsFiltered' => $product->countFiltered(),
				'data' => $data
			];

			return json_encode($result);
		}
	}

	public function create()
	{
		$validation = \Config\Services::validation();
		$request = Services::request();
		$eProduct = new \App\Entities\Product();

		$product = new M_Product($request);
		$pCategory = new M_ProductCategory();
		$principal = new M_Principal();

		$post = $request->getVar();
		$file = $request->getFile('url');

		try {
			if (!$validation->run($post, 'product')) {
				$response =	$this->field->errorValidation($this->table, $post);
			} else {
				$newfilename = '';

				if (!empty($file)) {
					$imgName = $file->getName();

					// Renaming file before upload
					$temp = explode(".", $imgName);
					$priData = $principal->find($post['md_principal_id']);
					$newfilename = strtolower($priData->name) . '_' . $post['code'] . '.' . end($temp);
					$post['url'] = $newfilename;
				}

				$eProduct->fill($post);
				$eProduct->visible = setCheckbox(isset($post['visible']));
				$eProduct->isactive = setCheckbox(isset($post['isactive']));

				$result = $product->save($eProduct);

				$post['id'] = $product->insertID();
				$pCategory->create($post, 'insert');

				// Move to folder
				$file->move($this->path_folder, $newfilename);

				$msg = $result ? 'Your data has been inserted successfully !' : $result;

				$response = message('success', true, $msg);
			}
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}

		return json_encode($response);
	}

	public function show($id)
	{
		$request = Services::request();
		$product = new M_Product($request);
		$list = $product->detail('md_product.md_product_id', $id, $this->path_folder);
		$response = $this->field->store($this->table, $list->getResult(), $list);
		return json_encode($response);
	}

	public function edit()
	{
		$validation = \Config\Services::validation();
		$request = Services::request();
		$eProduct = new \App\Entities\Product();

		$product = new M_Product($request);
		$pCategory = new M_ProductCategory();
		$principal = new M_Principal();

		$post = $request->getVar();
		$file = $request->getFile('url');

		$imgName = '';
		$newfilename = '';
		$rules = [];

		$rules = [
			'code' => [
				'rules' 	=>	'required|is_unique[md_product.code,md_product_id,{id}]',
				'errors' 	=> [
					'is_unique' => 'This {field} already exists.'
				]
			],
			'name' => [
				'rules' 	=>	'required|is_unique[md_product.name,md_product_id,{id}]',
				'errors' 	=> [
					'is_unique' => 'This {field} already exists.'
				]
			],
			'm_product_id' => [
				'label'		=> 'iDempiere code',
				'rules' 	=>	'required|is_unique[md_product.m_product_id,md_product_id,{id}]',
				'errors' 	=> [
					'is_unique' => 'This {field} already exists.'
				]
			],
			'md_uom_id' => [
				'label'		=> 	'uom',
				'rules'		=>	'required'
			],
			'md_principal_id' => [
				'label'		=> 	'principal',
				'rules'		=>	'required'
			],
			'description' => [
				'rules'		=>	'required'
			],
			'height' => [
				'rules'		=>	'max_length[7]'
			],
			'weight' => [
				'rules'		=>	'max_length[7]'
			],
			'depth' => [
				'rules'		=>	'max_length[7]'
			],
			'width' => [
				'rules'		=>	'max_length[7]'
			],
			'volume' => [
				'rules'		=>	'max_length[7]'
			]
		];

		if (!empty($file)) {
			$imgName = $file->getName();

			// Renaming file before upload
			$temp = explode(".", $imgName);
			$priData = $principal->find($post['md_principal_id']);
			$newfilename = strtolower($priData->name) . '_' . $post['code'] . '.' . end($temp);
		}

		// Check if upload new image
		if (!empty($imgName)) {
			$rules['url'] = [
				'label'		=>	'product image',
				'rules'		=>	'max_size[url, 3024]|is_image[url]|mime_in[url,image/jpg,image/jpeg,image/png]'
			];
			$post['url'] = $newfilename;
		} else if (!empty($post['url'])) {
			$post['url'];
		} else {
			$rules['url'] = [
				'label'		=>	'product image',
				'rules'		=>	'uploaded[url]|max_size[url, 3024]|is_image[url]|mime_in[url,image/jpg,image/jpeg,image/png]'
			];
		}

		$validation->setRules($rules);

		try {
			$eProduct->fill($post);
			$eProduct->md_product_id = $post['id'];
			$eProduct->visible = setCheckbox(isset($post['visible']));
			$eProduct->isactive = setCheckbox(isset($post['isactive']));

			if (!$validation->withRequest($request)->run()) {
				$response =	$this->field->errorValidation($this->table, $post);
			} else {
				$row = $product->find($post['id']);
				$oldImage = $this->path_folder . $row->url;

				// Change image new upload
				if (!empty($imgName)) {
					if (!empty($row->url) && file_exists($oldImage)) {
						unlink($oldImage);
					}
					$file->move($this->path_folder, $newfilename);
				}

				$result = $product->save($eProduct);
				$pCategory->create($post, 'update');

				$msg = $result ? 'Your data has been updated successfully !' : $result;

				$response = message('success', true, $msg);
			}
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}

		return json_encode($response);
	}

	public function destroy($id)
	{
		$request = Services::request();
		$product = new M_Product($request);
		$pCategory = new M_ProductCategory();

		try {
			$row = $product->find($id);
			$oldImage = $this->path_folder . $row->url;

			if (!empty($row->url) && file_exists($oldImage))
				unlink($this->path_folder . $row->url);

			$result = $product->delete($id);
			$pCategory->where('md_product_id', $id)->delete();

			$response = message('success', true, $result);
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}

		return json_encode($response);
	}

	public function getCategory()
	{
		$category = new M_Category();
		$post = $this->request->getVar();

		try {
			$result = $category->getDetail($post['principal'], $post['level']);
			$response = message('success', true, $result->getResult());
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}

		return json_encode($response);
	}

	public function export()
	{
		$request = Services::request();
		$product = new M_Product($request);
		$post = $request->getVar();

		if ($request->getMethod(true) === 'POST') {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setTitle('Sheet 1');

			$sheet->setCellValue('A1', 'Code')
				->setCellValue('B1', 'Name')
				->setCellValue('C1', 'Vendor')
				->setCellValue('D1', 'Category1')
				->setCellValue('E1', 'Category2')
				->setCellValue('F1', 'Category3')
				->setCellValue('G1', 'Description')
				->setCellValue('H1', 'Url')
				->setCellValue('I1', 'Url Tokopedia')
				->setCellValue('J1', 'Url Shopee')
				->setCellValue('K1', 'Url Lazada');

			$column = 2;
			$list = $product->detail(null, null, null, $post)->getResult();

			foreach ($list as $row) :
				// Convert html tag to plain text
				$convertHtml = new Html2Text($row->description);

				$sheet->setCellValue('A' . $column, $row->code)
					->setCellValue('B' . $column, $row->name)
					->setCellValue('C' . $column, $row->principal)
					->setCellValue('D' . $column, $row->md_category1)
					->setCellValue('E' . $column, $row->md_category2)
					->setCellValue('F' . $column, $row->md_category3)
					->setCellValue('G' . $column, $convertHtml->getText())
					->setCellValue('H' . $column, $row->path)
					->setCellValue('I' . $column, $row->url_toped)
					->setCellValue('J' . $column, $row->url_shopee)
					->setCellValue('K' . $column, $row->url_lazada);

				$column++;
			endforeach;

			$writer = new Xlsx($spreadsheet);

			$filename = 'Master Product' . date('Ymd' . '_' . 'His') . '.xlsx'; //save our workbook as this file name

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			ob_flush();

			$writer->save('php://output');

			exit;
		}
	}
}
