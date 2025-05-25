import React from 'react';
import { Link } from '@inertiajs/react';
import { Bars3Icon, ArrowRightStartOnRectangleIcon } from '@heroicons/react/24/outline';

export default function TopBar({ 
    title, 
    user, 
    onMenuClick 
}) {
    // Log user data for debugging
    console.log('TopBar user data:', user);

    if (!user) {
        console.warn('No user data provided to TopBar');
        return null;
    }

    return (
        <div className="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow">
            <button
                type="button"
                className="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
                onClick={onMenuClick}
            >
                <Bars3Icon className="h-6 w-6" />
            </button>
            <div className="flex flex-1 justify-between px-4">
                <div className="flex flex-1">
                    <h1 className="text-2xl font-semibold text-gray-900 my-auto">{title}</h1>
                </div>
                <div className="ml-4 flex items-center md:ml-6">
                    <div className="relative ml-3">
                        <div className="flex items-center space-x-4">
                            {/* User role indicator */}
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span className="text-sm font-medium text-gray-600">
                                            {user.name?.charAt(0).toUpperCase() || '?'}
                                        </span>
                                    </div>
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm font-medium text-gray-700">{user.name || 'Unknown User'}</p>
                                    <p className="text-xs text-gray-500 capitalize">{user.role || 'No Role'}</p>
                                </div>
                            </div>
                            <Link
                                href="/admin/logout"
                                method="post"
                                as="button"
                                className="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
                            >
                                <ArrowRightStartOnRectangleIcon className="h-5 w-5 mr-1" />
                                Logout
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
} 