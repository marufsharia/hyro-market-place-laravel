import { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';

export default function Navbar() {
    const { auth } = usePage().props;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [darkMode, setDarkMode] = useState(false);

    // Initialize Dark Mode from local storage
    useEffect(() => {
        if (
            localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            setDarkMode(true);
            document.documentElement.classList.add('dark');
        } else {
            setDarkMode(false);
            document.documentElement.classList.remove('dark');
        }
    }, []);

    const toggleDarkMode = () => {
        if (darkMode) {
            localStorage.theme = 'light';
            document.documentElement.classList.remove('dark');
            setDarkMode(false);
        } else {
            localStorage.theme = 'dark';
            document.documentElement.classList.add('dark');
            setDarkMode(true);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            router.get(route('market.index'), { search: searchQuery }, { preserveState: false });
        }
    };

    const handleLogout = (e) => {
        e.preventDefault();
        router.post(route('logout'));
    };

    return (
        <nav className="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors duration-300">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex justify-between h-16">

                    {/* Left: Logo & Mobile Menu Button */}
                    <div className="flex">
                        <div className="flex-shrink-0 flex items-center">
                            <Link href={route('market.index')} className="flex items-center gap-2 group">
                                <div className="bg-teal-600 text-white p-1.5 rounded-lg group-hover:bg-teal-500 transition-colors">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <span className="font-bold text-xl tracking-tight text-slate-900 dark:text-white">Hyro Market</span>
                            </Link>
                        </div>

                        {/* Desktop Navigation Links */}
                        <div className="hidden sm:ml-8 sm:flex sm:space-x-8">
                            <Link href={route('market.index')} className="border-transparent text-slate-500 dark:text-slate-300 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                                Plugins
                            </Link>
                            {auth?.user && (
                                <Link href={route('admin.dashboard')} className="border-transparent text-slate-500 dark:text-slate-300 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                                    Dashboard
                                </Link>
                            )}
                        </div>
                    </div>

                    {/* Center: Search (Desktop) */}
                    <div className="hidden md:flex flex-1 items-center justify-center px-8">
                        <form onSubmit={handleSearch} className="relative w-full max-w-md">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg className="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-full leading-5 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-500 focus:outline-none focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-all shadow-sm"
                                placeholder="Search plugins (e.g. 'Auth')..."
                            />
                        </form>
                    </div>

                    {/* Right: Actions (Dark Mode, Auth) */}
                    <div className="hidden sm:ml-6 sm:flex sm:items-center gap-4">
                        {/* Dark Mode Toggle */}
                        <button
                            onClick={toggleDarkMode}
                            className="p-2 rounded-full text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300 focus:outline-none transition-colors"
                            aria-label="Toggle Dark Mode"
                        >
                            {darkMode ? (
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            ) : (
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            )}
                        </button>

                        {/* Auth Logic */}
                        {auth.user ? (
                            <div className="ml-3 relative flex items-center gap-3">
                                <div className="text-right hidden lg:block">
                                    <div className="text-sm font-medium text-slate-900 dark:text-white">{auth.user.name}</div>
                                    <div className="text-xs text-slate-500 dark:text-slate-400">Developer</div>
                                </div>

                                <div className="relative ml-3">
                                    <div>
                                        <button
                                            type="button"
                                            className="flex max-w-xs items-center rounded-full bg-slate-800 dark:bg-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-600"
                                            id="user-menu-button"
                                            aria-expanded="false"
                                            aria-haspopup="true"
                                            onClick={() => setShowingNavigationDropdown((previousState) => !previousState)}
                                        >
                                            <span className="sr-only">Open user menu</span>
                                            <img className="h-8 w-8 rounded-full" src={auth.user.avatar || `https://ui-avatars.com/api/?name=${auth.user.name}&background=0D9488&color=fff`} alt="" />
                                        </button>
                                    </div>

                                    {/* Dropdown Menu */}
                                    {showingNavigationDropdown && (
                                        <div
                                            className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-slate-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                            role="menu"
                                            aria-orientation="vertical"
                                            aria-labelledby="user-menu-button"
                                            tabIndex="-1"
                                        >
                                            <Link
                                                href={route('profile.edit')}
                                                className="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"
                                                role="menuitem"
                                                tabIndex="-1"
                                                id="user-menu-item-0"
                                            >
                                                Profile
                                            </Link>
                                            <Link
                                                href={route('admin.dashboard')}
                                                className="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"
                                                role="menuitem"
                                                tabIndex="-1"
                                                id="user-menu-item-1"
                                            >
                                                Dashboard
                                            </Link>
                                            <form onSubmit={handleLogout}>
                                                <button
                                                    type="submit"
                                                    className="block w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"
                                                    role="menuitem"
                                                    tabIndex="-1"
                                                    id="user-menu-item-3"
                                                >
                                                    Log Out
                                                </button>
                                            </form>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <Link
                                href={route('login')}
                                className="flex items-center gap-2 bg-slate-900 dark:bg-teal-600 hover:bg-slate-800 dark:hover:bg-teal-700 text-white px-4 py-2 rounded-full text-sm font-medium transition-all shadow-md"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                Sign In
                            </Link>
                        )}
                    </div>

                    {/* Mobile Menu Button */}
                    <div className="-mr-2 flex items-center sm:hidden">
                        <button
                            onClick={() => setShowingNavigationDropdown((previousState) => !previousState)}
                            className="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-500"
                        >
                            <span className="sr-only">Open main menu</span>
                            {!showingNavigationDropdown ? (
                                <svg className="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            ) : (
                                <svg className="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden="true">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            )}
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile Menu Dropdown */}
            {showingNavigationDropdown && (
                <div className="sm:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                    <div className="pt-2 pb-3 space-y-1">
                        <Link href={route('market.index')} className="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-teal-500 hover:text-teal-700">
                            Plugins
                        </Link>
                        {auth?.user && (
                            <Link href={route('admin.dashboard')} className="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-teal-500 hover:text-teal-700">
                                Dashboard
                            </Link>
                        )}
                    </div>
                    <div className="pt-4 pb-4 border-t border-slate-200 dark:border-slate-800">
                        <div className="mt-3 space-y-1">
                            {auth.user ? (
                                <>
                                    <div className="flex items-center px-4">
                                        <div className="flex-shrink-0">
                                            <img className="h-10 w-10 rounded-full" src={auth.user.avatar || `https://ui-avatars.com/api/?name=${auth.user.name}&background=0D9488&color=fff`} alt="" />
                                        </div>
                                        <div className="ml-3">
                                            <div className="text-base font-medium text-slate-800 dark:text-white">{auth.user.name}</div>
                                            <div className="text-sm font-medium text-slate-500">{auth.user.email}</div>
                                        </div>
                                    </div>
                                    <div className="mt-3 space-y-1">
                                        <Link href={route('profile.edit')} className="block px-4 py-2 text-base font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Profile</Link>
                                        <form onSubmit={handleLogout}>
                                            <button className="block w-full text-left px-4 py-2 text-base font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Log Out</button>
                                        </form>
                                    </div>
                                </>
                            ) : (
                                <Link href={route('login')} className="block px-4 py-2 text-base font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                    Log in
                                </Link>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </nav>
    );
}