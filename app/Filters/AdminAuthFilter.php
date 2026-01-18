<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->get('admin_logged_in')) {
            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());
            
            return redirect()->to(base_url('admin/login'))
                            ->with('error', 'Please login to access admin area.');
        }
        
        // Check if user has admin role
        if ($session->get('admin_role') !== 'admin' && $session->get('admin_role') !== 'editor') {
            return redirect()->to(base_url())
                            ->with('error', 'You do not have permission to access this area.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
