import { Link, useForm } from '@inertiajs/react';
import Navbar from '@/Components/Navbar';
import { useState } from 'react';

export default function Home({ stats, featuredPlugins }) {
    const { data, get } = useForm({
        search: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (data.search.trim()) {
            get(route('market.index'), { search: data.search });
        }
    };

    return (
        <div className="min-h-screen bg-white dark:bg-slate-900 font-sans text-slate-900 dark:text-slate-100">

            {/* Navbar */}
            <Navbar />

            {/* Hero Section */}
            <div className="relative bg-slate-900 overflow-hidden">
                {/* Decorative background elements */}
                <div className="absolute inset-0">
                    <div className="absolute inset-y-0 right-0 w-1/2 bg-teal-900/10 rounded-l-full blur-3xl opacity-30"></div>
                </div>

                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 flex flex-col items-center text-center">
                    <div className="inline-flex items-center px-3 py-1 rounded-full border border-teal-500/30 bg-teal-500/10 text-teal-400 text-sm font-medium mb-6">
                        <span className="flex h-2 w-2 relative mr-2">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span className="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                        </span>
                        v2.0 is now available
                    </div>

                    <h1 className="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl lg:text-7xl">
                        <span className="block">Build faster with</span>
                        <span className="block text-teal-400">Hyro Marketplace</span>
                    </h1>

                    <p className="mt-6 max-w-lg mx-auto text-xl text-slate-300">
                        The official directory for Hyro packages. Discover, install, and manage high-quality plugins to extend your Laravel applications.
                    </p>

                    {/* Big Search Bar */}
                    <form onSubmit={handleSubmit} className="mt-10 w-full max-w-2xl relative group">
                        <div className="absolute -inset-1 bg-gradient-to-r from-teal-500 to-blue-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        <div className="relative flex items-center bg-white rounded-full shadow-2xl">
                            <input
                                type="text"
                                value={data.search}
                                onChange={(e) => data.search = e.target.value}
                                className="flex-1 appearance-none w-full py-4 px-6 bg-transparent text-slate-900 placeholder-slate-500 focus:outline-none text-lg"
                                placeholder="What are you looking for?"
                            />
                            <button type="submit" className="mr-2 bg-teal-600 text-white p-3 rounded-full hover:bg-teal-500 transition-colors">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </div>
                    </form>

                    <div className="mt-6 text-sm text-slate-400">
                        Trending: <Link href={route('market.index', { search: 'Authentication' })} className="hover:text-white underline decoration-teal-500/50">Authentication</Link>, <Link href={route('market.index', { search: 'Payment' })} className="hover:text-white underline decoration-teal-500/50">Payments</Link>, <Link href={route('market.index', { search: 'UI' })} className="hover:text-white underline decoration-teal-500/50">UI Kits</Link>
                    </div>
                </div>
            </div>

            {/* Stats Bar */}
            <div className="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div className="bg-white dark:bg-slate-900 overflow-hidden shadow rounded-lg border border-slate-100 dark:border-slate-700">
                            <div className="px-4 py-5 sm:p-6">
                                <dt className="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Total Plugins</dt>
                                <dd className="mt-1 text-3xl font-semibold text-slate-900 dark:text-white">{stats.plugins}</dd>
                            </div>
                        </div>
                        <div className="bg-white dark:bg-slate-900 overflow-hidden shadow rounded-lg border border-slate-100 dark:border-slate-700">
                            <div className="px-4 py-5 sm:p-6">
                                <dt className="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Total Downloads</dt>
                                <dd className="mt-1 text-3xl font-semibold text-slate-900 dark:text-white">{stats.downloads}</dd>
                            </div>
                        </div>
                        <div className="bg-white dark:bg-slate-900 overflow-hidden shadow rounded-lg border border-slate-100 dark:border-slate-700">
                            <div className="px-4 py-5 sm:p-6">
                                <dt className="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Community</dt>
                                <dd className="mt-1 text-3xl font-semibold text-slate-900 dark:text-white">1,240+</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Featured Section */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div className="text-center mb-12">
                    <h2 className="text-base text-teal-600 font-semibold tracking-wide uppercase">Featured</h2>
                    <p className="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        Most Popular Plugins
                    </p>
                    <p className="mt-4 max-w-2xl text-xl text-slate-500 dark:text-slate-400 mx-auto">
                        Discover the tools developers are using to power their applications.
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {featuredPlugins.map((plugin) => (
                        <Link
                            key={plugin.id}
                            href={route('market.show', plugin.slug)}
                            className="group bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300"
                        >
                            <div className="p-6">
                                <div className="flex items-center space-x-4">
                                    <div className="flex-shrink-0">
                                        <img
                                            className="h-12 w-12 rounded-lg object-cover bg-slate-100 dark:bg-slate-700"
                                            src={plugin.logo_path ? `/storage/${plugin.logo_path}` : `https://ui-avatars.com/api/?name=${plugin.name}&background=random`}
                                            alt=""
                                        />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-bold text-slate-900 dark:text-white group-hover:text-teal-500 transition-colors line-clamp-1">
                                            {plugin.name}
                                        </h3>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            ★ {plugin.rating_avg}
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-4">
                                    <p className="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 h-10">
                                        {plugin.description}
                                    </p>
                                </div>
                            </div>
                            <div className="bg-slate-50 dark:bg-slate-700/50 px-6 py-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <span className="text-xs text-slate-500 dark:text-slate-400">
                                    {plugin.downloads} installs
                                </span>
                                <span className="text-teal-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                                    View →
                                </span>
                            </div>
                        </Link>
                    ))}
                </div>

                <div className="mt-12 text-center">
                    <Link
                        href={route('market.index')}
                        className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-teal-700 bg-teal-100 hover:bg-teal-200 md:py-4 md:text-lg md:px-10 transition-colors"
                    >
                        View All Plugins
                    </Link>
                </div>
            </div>

            {/* Features Grid */}
            <div className="bg-slate-50 dark:bg-slate-800 py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="lg:text-center mb-12">
                        <h2 className="text-base text-teal-600 font-semibold tracking-wide uppercase">Why Hyro?</h2>
                        <p className="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                            Built for Developers
                        </p>
                    </div>

                    <div className="mt-10">
                        <dl className="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                            <div className="relative">
                                <dt>
                                    <div className="absolute flex items-center justify-center h-12 w-12 rounded-md bg-teal-500 text-white">
                                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </div>
                                    <p className="ml-16 text-lg leading-6 font-medium text-slate-900 dark:text-white">Lightning Fast</p>
                                </dt>
                                <dd className="mt-2 ml-16 text-base text-slate-500 dark:text-slate-400">
                                    Optimized for performance. Every plugin is reviewed to ensure it meets high-speed standards.
                                </dd>
                            </div>

                            <div className="relative">
                                <dt>
                                    <div className="absolute flex items-center justify-center h-12 w-12 rounded-md bg-teal-500 text-white">
                                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p className="ml-16 text-lg leading-6 font-medium text-slate-900 dark:text-white">Secure by Default</p>
                                </dt>
                                <dd className="mt-2 ml-16 text-base text-slate-500 dark:text-slate-400">
                                    Automated vulnerability scanning and manual code review for every submission.
                                </dd>
                            </div>

                            <div className="relative">
                                <dt>
                                    <div className="absolute flex items-center justify-center h-12 w-12 rounded-md bg-teal-500 text-white">
                                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <p className="ml-16 text-lg leading-6 font-medium text-slate-900 dark:text-white">Community Driven</p>
                                </dt>
                                <dd className="mt-2 ml-16 text-base text-slate-500 dark:text-slate-400">
                                    Created by developers, for developers. Open source contributions are encouraged.
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {/* CTA Section */}
            <div className="bg-teal-700">
                <div className="max-w-2xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
                    <h2 className="text-3xl font-extrabold text-white sm:text-4xl">
                        <span className="block">Ready to dive in?</span>
                        <span className="block">Start your free trial today.</span>
                    </h2>
                    <p className="mt-4 text-lg leading-6 text-teal-200">
                        Join thousands of developers building the next generation of web apps with Hyro.
                    </p>
                    <Link
                        href={route('market.index')}
                        className="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-teal-700 bg-white hover:bg-teal-50 sm:w-auto"
                    >
                        Explore Marketplace
                    </Link>
                </div>
            </div>

            {/* Simple Footer for Landing Page */}
            <footer className="bg-slate-900 border-t border-slate-800 py-8">
                <div className="max-w-7xl mx-auto px-4 text-center text-slate-500 text-sm">
                    &copy; {new Date().getFullYear()} Hyro Market Place. All rights reserved.
                </div>
            </footer>
        </div>
    );
}