<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\M_News;
use App\Models\M_image;

class News extends BaseController
{
	protected $table = 'trx_news';
	protected $path_folder = 'custom/image/news/';

	public function index()
	{
		$this->new_title = 'News';
		$this->form_type = 'new_form';

		$data = [
			'title'    	=> '' . $this->new_title . '',
			'button'    => '<button type="button" class="btn btn-primary btn-sm btn-round ml-auto ' . $this->form_type . ' ' . $this->modal_type . '" title="' . $this->new_title . '">
												<i class="fa fa-plus fa-fw"></i> ' . $this->new_title . '
										 </button>',
		];
		return $this->template->render('backend/news/v_news', $data);
	}

	public function showAll()
	{
		$news = new M_News();
		$list = $news->findAll();
		$data = [];

		$number = 0;
		foreach ($list as $value) :
			$row = [];
			$ID = $value->trx_news_id;

			$number++;

			$row[] = $ID;
			$row[] = $number;
			$row[] = $value->title;
			$row[] = $value->news_date;
			$row[] = active($value->isactive);
			$row[] = '<center>
						<a class="btn" onclick="Edit(' . "'" . $ID . "'" . ')" title="Edit"><i class="far fa-edit text-info"></i></a>
						<a class="btn" onclick="Destroy(' . "'" . $ID . "'" . ')" title="Delete"><i class="fas fa-trash-alt text-danger"></i></a>
					</center>';
			$data[] = $row;
		endforeach;

		$result = array('data' => $data);
		return json_encode($result);
	}

	public function create()
	{
		$validation = \Config\Services::validation();
		$eNews = new \App\Entities\News();

		$news = new M_News();
		$image = new M_image();

		$post = $this->request->getVar();

		$file = $this->request->getFile('md_image_id');
		$imgName = $file->getName();

		// Renaming file before upload
		$temp = explode(".", $imgName);
		$newfilename = round(microtime(true)) . '.' . end($temp);

		// Mapping to property
		$post['md_image_id'] = $newfilename;

		try {
			$eNews->fill($post);
			$eNews->isactive = setCheckbox(isset($post['isactive']));

			if (!$validation->run($post, 'news')) {
				$response =	$this->field->errorValidation($this->table);
			} else {
				$image_id = $image->insert_image($newfilename, $this->path_folder);

				if (isset($image_id)) {
					$eNews->md_image_id = $image_id;
				}
				$result = $news->save($eNews);

				// Move to folder
				$file->move($this->path_folder, $newfilename);

				$response = message('success', true, $result);
			}
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}
		return json_encode($response);
	}

	public function show($id)
	{
		$news = new M_News();
		$list = $news->detail('trx_news_id', $id);
		$reponse = $this->field->store($this->table, $list->getResult(), $list);
		return json_encode($reponse);
	}

	public function edit()
	{
		$validation = \Config\Services::validation();
		$eNews = new \App\Entities\News();
		$news = new M_News();
		$image = new M_image();

		$image_id = 0;

		$post = $this->request->getVar();
		$row = $news->detail('trx_news_id', $post['id'])->getRow();

		$file = $this->request->getFile('md_image_id');
		$imgName = $file->getName();

		// Renaming file before upload
		$temp = explode(".", $imgName);
		$newfilename = round(microtime(true)) . '.' . end($temp);

		$validation->setRules([
			'title' => [
				'label'		=> 'news title',
				'rules' 	=> 'required'
			],
			'content' => [
				'label'		=> 'news content',
				'rules' 	=> 'required'
			],
			'news_date' => [
				'label'		=> 'posted date',
				'rules' 	=> 'required'
			],
			'slug' => [
				'label'		=> 'slug',
				'rules' 	=> 'required'
			],
		]);

		// Check if upload new image
		if (!empty($imgName)) {
			$post['md_image_id'] = $newfilename;

			$validation->setRules([
				'md_image_id' => [
					'label'		=>	'image',
					'rules'		=>	'max_size[md_image_id, 1024]|is_image[md_image_id]'
					// 'rules'		=>	'uploaded[md_image_id]|max_size[md_image_id, 1024]|is_image[md_image_id]|mime_in[md_image_id,image/jpg,image/jpeg,image/png]'
				]
			]);
		} else {
			// Check empty image post
			if (!empty($post['md_image_id'])) {
				$image_id = $row->image_id;
			} else {
				$validation->setRules([
					'md_image_id' => [
						'label'		=>	'image',
						'rules'		=>	'uploaded[md_image_id]|max_size[md_image_id, 1024]|is_image[md_image_id]|mime_in[md_image_id,image/jpg,image/jpeg,image/png]'
					]
				]);
				$image_id = $post['md_image_id'];
			}
		}

		try {
			$eNews->fill($post);
			$eNews->trx_news_id = $post['id'];
			$eNews->isactive = setCheckbox(isset($post['isactive']));

			if (isset($image_id)) {
				$eNews->md_image_id = $image_id;
			}

			if (!$validation->withRequest($this->request)->run()) {
				$response =	$this->field->errorValidation($this->table);
			} else {
				if (!empty($imgName)) {
					// Remove old image path directory
					unlink($this->path_folder . $row->image);

					// Delete old image data
					$delete = $image->delete($row->image_id);

					if ($delete) {
						// Insert new data image
						$image_id = $image->insert_image($newfilename, $this->path_folder);

						// Move to folder
						$file->move($this->path_folder, $newfilename);
					}
				}
				$result = $news->save($eNews);
				$response = message('success', true, $result);
			}
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}

		return json_encode($response);
	}

	public function destroy($id)
	{
		$news = new M_News();

		try {
			$result = $news->delete($id);
			$response = message('success', true, $result);
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}
		return json_encode($response);
	}
}
