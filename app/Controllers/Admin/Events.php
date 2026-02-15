<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;

class Events extends BaseController
{
    protected $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    /**
     * List all events
     */
    public function index()
    {
        $data = [
            'page_title' => 'Events Coming Up',
            'events' => $this->eventModel->getAllOrdered()
        ];

        return view('admin/events/index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Create Event'
        ];

        return view('admin/events/create', $data);
    }

    /**
     * Store new event
     */
    public function store()
    {
        $rules = [
            'title'      => 'required|min_length[3]|max_length[500]',
            'event_date' => 'required|valid_date',
            'status'     => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $slug = $this->eventModel->generateSlug($title);

        $eventData = [
            'title'          => $title,
            'slug'           => $slug,
            'excerpt'        => $this->request->getPost('excerpt'),
            'content'        => $this->request->getPost('content'),
            'event_date'     => $this->request->getPost('event_date'),
            'event_time'     => $this->request->getPost('event_time') ?: null,
            'event_end_date' => $this->request->getPost('event_end_date') ?: null,
            'event_end_time' => $this->request->getPost('event_end_time') ?: null,
            'location'       => $this->request->getPost('location'),
            'status'         => $this->request->getPost('status'),
            'author_id'      => session()->get('admin_id'),
        ];

        $uploadDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            $newName = $featuredImage->getRandomName();
            $featuredImage->move($uploadDir, $newName);
            $eventData['featured_image'] = $newName;
        }

        $eventId = $this->eventModel->insert($eventData);

        if (!$eventId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create event.');
        }

        return redirect()->to(base_url('admin/events'))
            ->with('success', 'Event created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Event not found.');
        }

        $data = [
            'page_title' => 'Edit Event',
            'event' => $event
        ];

        return view('admin/events/edit', $data);
    }

    /**
     * Update event
     */
    public function update($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Event not found.');
        }

        $rules = [
            'title'      => 'required|min_length[3]|max_length[500]',
            'event_date' => 'required|valid_date',
            'status'     => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $slug = $event['slug'];
        if ($title !== $event['title']) {
            $slug = $this->eventModel->generateSlug($title, (int) $id);
        }

        $eventData = [
            'title'          => $title,
            'slug'           => $slug,
            'excerpt'        => $this->request->getPost('excerpt'),
            'content'        => $this->request->getPost('content'),
            'event_date'     => $this->request->getPost('event_date'),
            'event_time'     => $this->request->getPost('event_time') ?: null,
            'event_end_date' => $this->request->getPost('event_end_date') ?: null,
            'event_end_time' => $this->request->getPost('event_end_time') ?: null,
            'location'       => $this->request->getPost('location'),
            'status'         => $this->request->getPost('status'),
        ];

        $uploadDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            if ($event['featured_image']) {
                $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $event['featured_image'];
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                } else {
                    $publicPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR . $event['featured_image'];
                    if (file_exists($publicPath)) {
                        @unlink($publicPath);
                    }
                }
            }
            $newName = $featuredImage->getRandomName();
            $featuredImage->move($uploadDir, $newName);
            $eventData['featured_image'] = $newName;
        }

        $this->eventModel->update($id, $eventData);

        return redirect()->to(base_url('admin/events'))
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Delete event
     */
    public function delete($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Event not found.');
        }

        $uploadDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events';
        $publicDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events';
        if ($event['featured_image']) {
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . $event['featured_image'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            } else {
                $publicPath = $publicDir . DIRECTORY_SEPARATOR . $event['featured_image'];
                if (file_exists($publicPath)) {
                    @unlink($publicPath);
                }
            }
        }

        $this->eventModel->delete($id);

        return redirect()->to(base_url('admin/events'))
            ->with('success', 'Event deleted successfully.');
    }
}
