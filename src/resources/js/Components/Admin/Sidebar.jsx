import React from 'react';
import { Link } from '@inertiajs/react';
import { 
    HomeIcon, 
    QuestionMarkCircleIcon, 
    UserGroupIcon, 
    ChartBarIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    XMarkIcon
} from '@heroicons/react/24/outline';

export default function Sidebar({ 
    url, 
    user, 
    expandedMenus, 
    toggleMenu, 
    isMobile = false, 
    onClose = () => {} 
}) {
    // Base navigation items that all roles can see
    const baseNavigation = [
        { 
            name: 'Dashboard', 
            href: '/admin/dashboard', 
            icon: HomeIcon, 
            current: url.startsWith('/admin/dashboard'),
            roles: ['manager', 'corrector', 'general']
        }
    ];

    // Role-specific navigation items
    const roleNavigation = [
        // Questions menu with submenu
        {
            name: 'Questions',
            icon: QuestionMarkCircleIcon,
            current: url.startsWith('/admin/questions'),
            roles: ['manager', 'corrector', 'general'],
            children: [
                {
                    name: 'All Questions',
                    href: '/admin/questions',
                    current: url.startsWith('/admin/questions') && !url.includes('status=pending'),
                    roles: ['manager', 'corrector', 'general']
                },
                {
                    name: 'Pending Questions',
                    href: '/admin/questions?status=pending',
                    current: url.startsWith('/admin/questions') && url.includes('status=pending'),
                    roles: ['manager', 'corrector']
                }
            ]
        },
        // Manager only items
        {
            name: 'AdminUsers',
            href: '/admin/users',
            icon: UserGroupIcon,
            current: url.startsWith('/admin/users'),
            roles: ['manager']
        },
        {
            name: 'Clients',
            href: '/admin/clients',
            icon: UserGroupIcon,
            current: url.startsWith('/admin/clients'),
            roles: ['manager']
        },
        {
            name: 'Reports',
            icon: ChartBarIcon,
            current: url.startsWith('/admin/questions'),
            roles: ['manager'],
            children: [
                {
                    name: 'Game Reports',
                    href: '/admin/game-reports',
                    current: url.startsWith('/admin/game-reports'),
                    roles: ['manager', 'corrector', 'general']
                },

            ]
        },
    ];

    // Combine and filter navigation based on user role
    const navigation = [...baseNavigation, ...roleNavigation].filter(item => 
        item.roles.includes(user?.role)
    );

    const renderNavigationItem = (item) => {
        if (item.children) {
            return (
                <div key={item.name}>
                    <button
                        onClick={() => toggleMenu(item.name.toLowerCase())}
                        className={`w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                            item.current
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                        }`}
                    >
                        <item.icon
                            className={`h-6 w-6 flex-shrink-0 ${
                                item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500'
                            }`}
                        />
                        <span className="ml-3">{item.name}</span>
                        {expandedMenus[item.name.toLowerCase()] ? (
                            <ChevronDownIcon className="ml-auto h-5 w-5 text-gray-400" />
                        ) : (
                            <ChevronRightIcon className="ml-auto h-5 w-5 text-gray-400" />
                        )}
                    </button>
                    {expandedMenus[item.name.toLowerCase()] && (
                        <div className="ml-4 mt-1 space-y-1">
                            {item.children
                                .filter(child => child.roles.includes(user?.role))
                                .map(child => (
                                    <Link
                                        key={child.name}
                                        href={child.href}
                                        className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                                            child.current
                                                ? 'bg-gray-100 text-gray-900'
                                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                        }`}
                                    >
                                        {child.name}
                                    </Link>
                                ))}
                        </div>
                    )}
                </div>
            );
        }

        return (
            <Link
                key={item.name}
                href={item.href}
                className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                    item.current
                        ? 'bg-gray-100 text-gray-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                }`}
            >
                <item.icon
                    className={`mr-3 h-6 w-6 flex-shrink-0 ${
                        item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500'
                    }`}
                />
                {item.name}
            </Link>
        );
    };

    const sidebarContent = (
        <>
            <div className="flex h-16 items-center justify-between px-4">
                <Link href="/admin/dashboard" className="flex items-center">
                    <span className="text-xl font-bold text-gray-900">Quiz Admin</span>
                </Link>
                {isMobile && (
                    <button
                        type="button"
                        className="text-gray-500 hover:text-gray-600"
                        onClick={onClose}
                    >
                        <XMarkIcon className="h-6 w-6" />
                    </button>
                )}
            </div>
            <nav className="flex-1 space-y-1 px-2 py-4">
                {navigation.map(renderNavigationItem)}
            </nav>
        </>
    );

    if (isMobile) {
        return (
            <div className="fixed inset-0 z-40 lg:hidden">
                <div className="fixed inset-0 bg-gray-600 bg-opacity-75" onClick={onClose} />
                <div className="fixed inset-y-0 left-0 flex w-64 flex-col bg-white">
                    {sidebarContent}
                </div>
            </div>
        );
    }

    return (
        <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div className="flex min-h-0 flex-1 flex-col border-r border-gray-200 bg-white">
                {sidebarContent}
            </div>
        </div>
    );
} 