import React, { useEffect } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Test({ auth }) {
    useEffect(() => {
        console.log('Test component mounted');
        console.log('Auth data:', auth);
    }, [auth]);

    return (
        <AdminLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-2xl font-semibold mb-4">Test Page</h1>
                            <pre className="bg-gray-100 p-4 rounded">
                                {JSON.stringify({ auth }, null, 2)}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 