import { Head, Link } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import Navbar from '@/Components/Navbar';

export default function Show({ doc, prevDoc, nextDoc, relatedDocs, categories }) {
    const [activeHeading, setActiveHeading] = useState('');
    const [tableOfContents, setTableOfContents] = useState([]);

    useEffect(() => {
        // Extract headings from content for table of contents
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = doc.content;
        const headings = tempDiv.querySelectorAll('h2, h3');
        
        const toc = Array.from(headings).map((heading, index) => ({
            id: `heading-${index}`,
            text: heading.textContent,
            level: heading.tagName.toLowerCase()
        }));
        
        setTableOfContents(toc);

        // Add IDs to actual headings in the content
        const contentDiv = document.getElementById('doc-content');
        if (contentDiv) {
            const actualHeadings = contentDiv.querySelectorAll('h2, h3');
            actualHeadings.forEach((heading, index) => {
                heading.id = `heading-${index}`;
            });
        }

        // Intersection Observer for active heading
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setActiveHeading(entry.target.id);
                    }
                });
            },
            { rootMargin: '-100px 0px -80% 0px' }
        );

        if (contentDiv) {
            const headingsToObserve = contentDiv.querySelectorAll('h2, h3');
            headingsToObserve.forEach((heading) => observer.observe(heading));
        }

        return () => observer.disconnect();
    }, [doc.content]);

    const scrollToHeading = (id) => {
        const element = document.getElementById(id);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900">
            <Head title={`${doc.title} - Documentation`} />
            <Navbar />

            <div className="max-w-7xl mx-auto px-4 py-8">
                <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    {/* Sidebar - Categories */}
                    <aside className="lg:col-span-1">
                        <div className="sticky top-4 space-y-6">
                            <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-4">
                                <h3 className="font-bold text-slate-900 dark:text-white mb-3">Categories</h3>
                                <nav className="space-y-1">
                                    {categories.map(category => (
                                        <div key={category.id}>
                                            <Link
                                                href={route('docs.index', { category: category.slug })}
                                                className={`block px-3 py-2 rounded text-sm transition-colors ${
                                                    doc.category_id === category.id
                                                        ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-medium'
                                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'
                                                }`}
                                            >
                                                {category.icon} {category.name}
                                            </Link>
                                            {doc.category_id === category.id && category.published_documentations && (
                                                <div className="ml-4 mt-1 space-y-1">
                                                    {category.published_documentations.map(catDoc => (
                                                        <Link
                                                            key={catDoc.id}
                                                            href={route('docs.show', catDoc.slug)}
                                                            className={`block px-3 py-1 rounded text-xs transition-colors ${
                                                                doc.id === catDoc.id
                                                                    ? 'text-teal-600 dark:text-teal-400 font-medium'
                                                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'
                                                            }`}
                                                        >
                                                            {catDoc.title}
                                                        </Link>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </nav>
                            </div>
                        </div>
                    </aside>

                    {/* Main Content */}
                    <main className="lg:col-span-2">
                        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8">
                            {/* Breadcrumb */}
                            <div className="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6">
                                <Link href={route('docs.index')} className="hover:text-teal-600">
                                    Documentation
                                </Link>
                                <span>/</span>
                                <Link 
                                    href={route('docs.index', { category: doc.category.slug })} 
                                    className="hover:text-teal-600"
                                >
                                    {doc.category.name}
                                </Link>
                                <span>/</span>
                                <span className="text-slate-900 dark:text-white">{doc.title}</span>
                            </div>

                            {/* Header */}
                            <div className="mb-8">
                                <div className="flex items-center gap-3 mb-4">
                                    <h1 className="text-4xl font-bold text-slate-900 dark:text-white">
                                        {doc.title}
                                    </h1>
                                    <span className="text-sm bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full text-slate-600 dark:text-slate-400">
                                        v{doc.version}
                                    </span>
                                </div>
                                {doc.excerpt && (
                                    <p className="text-lg text-slate-600 dark:text-slate-400">
                                        {doc.excerpt}
                                    </p>
                                )}
                                <div className="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400 mt-4">
                                    <span>👁️ {doc.views} views</span>
                                    <span>•</span>
                                    <span>📅 {new Date(doc.updated_at).toLocaleDateString()}</span>
                                </div>
                            </div>

                            {/* Content */}
                            <div 
                                id="doc-content"
                                className="prose dark:prose-invert max-w-none prose-headings:font-bold prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4 prose-h3:text-xl prose-h3:mt-6 prose-h3:mb-3 prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-a:text-teal-600 prose-a:no-underline hover:prose-a:underline prose-code:bg-slate-100 dark:prose-code:bg-slate-900 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-pre:bg-slate-900 dark:prose-pre:bg-slate-950 prose-pre:text-slate-100"
                                dangerouslySetInnerHTML={{ __html: doc.content }}
                            />

                            {/* Tags */}
                            {doc.tags && doc.tags.length > 0 && (
                                <div className="mt-8 pt-8 border-t border-slate-200 dark:border-slate-700">
                                    <div className="flex flex-wrap gap-2">
                                        {doc.tags.map((tag, index) => (
                                            <span
                                                key={index}
                                                className="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm"
                                            >
                                                #{tag}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Navigation */}
                            <div className="mt-8 pt-8 border-t border-slate-200 dark:border-slate-700 flex justify-between gap-4">
                                {prevDoc ? (
                                    <Link
                                        href={route('docs.show', prevDoc.slug)}
                                        className="flex-1 p-4 bg-slate-50 dark:bg-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
                                    >
                                        <div className="text-sm text-slate-500 dark:text-slate-400 mb-1">← Previous</div>
                                        <div className="font-medium text-slate-900 dark:text-white">{prevDoc.title}</div>
                                    </Link>
                                ) : (
                                    <div className="flex-1"></div>
                                )}
                                {nextDoc ? (
                                    <Link
                                        href={route('docs.show', nextDoc.slug)}
                                        className="flex-1 p-4 bg-slate-50 dark:bg-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors text-right"
                                    >
                                        <div className="text-sm text-slate-500 dark:text-slate-400 mb-1">Next →</div>
                                        <div className="font-medium text-slate-900 dark:text-white">{nextDoc.title}</div>
                                    </Link>
                                ) : (
                                    <div className="flex-1"></div>
                                )}
                            </div>
                        </div>
                    </main>

                    {/* Right Sidebar - Table of Contents */}
                    <aside className="lg:col-span-1">
                        <div className="sticky top-4 space-y-6">
                            {/* Table of Contents */}
                            {tableOfContents.length > 0 && (
                                <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-4">
                                    <h3 className="font-bold text-slate-900 dark:text-white mb-3">On This Page</h3>
                                    <nav className="space-y-1">
                                        {tableOfContents.map((item) => (
                                            <button
                                                key={item.id}
                                                onClick={() => scrollToHeading(item.id)}
                                                className={`block w-full text-left px-3 py-1 rounded text-sm transition-colors ${
                                                    item.level === 'h3' ? 'pl-6' : ''
                                                } ${
                                                    activeHeading === item.id
                                                        ? 'text-teal-600 dark:text-teal-400 font-medium'
                                                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                                }`}
                                            >
                                                {item.text}
                                            </button>
                                        ))}
                                    </nav>
                                </div>
                            )}

                            {/* Related Articles */}
                            {relatedDocs && relatedDocs.length > 0 && (
                                <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-4">
                                    <h3 className="font-bold text-slate-900 dark:text-white mb-3">Related Articles</h3>
                                    <div className="space-y-2">
                                        {relatedDocs.map(related => (
                                            <Link
                                                key={related.id}
                                                href={route('docs.show', related.slug)}
                                                className="block p-2 rounded text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors"
                                            >
                                                {related.title}
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    );
}
