import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Navbar from '@/Components/Navbar';

export default function Show({ plugin, userReview, auth }) {
    const { flash } = usePage().props;
    const [isFavorited, setIsFavorited] = useState(plugin.is_favorited || false);
    const [favoriteLoading, setFavoriteLoading] = useState(false);
    const [selectedRating, setSelectedRating] = useState(userReview?.rating || 0);
    const [hoverRating, setHoverRating] = useState(0);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        rating: userReview?.rating || '',
        comment: userReview?.comment || '',
    });

    const handleDownload = () => {
        router.post(route('market.download', plugin.slug));
    };

    const handleFavoriteToggle = async () => {
        if (!auth.user) {
            router.visit(route('login'));
            return;
        }

        setFavoriteLoading(true);
        
        router.post(
            route('favorites.toggle', plugin.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsFavorited(!isFavorited);
                },
                onFinish: () => {
                    setFavoriteLoading(false);
                },
            }
        );
    };

    const handleReviewSubmit = (e) => {
        e.preventDefault();
        
        if (!auth.user) {
            router.visit(route('login'));
            return;
        }

        post(route('reviews.store', plugin.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (!userReview) {
                    reset();
                    setSelectedRating(0);
                }
            },
        });
    };

    const renderStars = (rating) => {
        return (
            <span className="text-amber-500">
                {'★'.repeat(Math.floor(rating))}
                {'☆'.repeat(5 - Math.floor(rating))}
            </span>
        );
    };

    const renderRatingSelector = () => {
        return (
            <div className="flex gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <button
                        key={star}
                        type="button"
                        onClick={() => {
                            setSelectedRating(star);
                            setData('rating', star);
                        }}
                        onMouseEnter={() => setHoverRating(star)}
                        onMouseLeave={() => setHoverRating(0)}
                        className="text-3xl focus:outline-none transition-colors"
                    >
                        <span className={
                            star <= (hoverRating || selectedRating || data.rating)
                                ? 'text-amber-500'
                                : 'text-slate-300 dark:text-slate-600'
                        }>
                            ★
                        </span>
                    </button>
                ))}
            </div>
        );
    };

    const canReview = auth.user && plugin.user_id !== auth.user.id;

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900 font-sans">
            <Head title={`${plugin.name} - Hyro Market`} />
            <Navbar />

            <div className="max-w-4xl mx-auto px-4 py-8">
                <Link href={route('market.index')} className="text-teal-600 hover:underline text-sm mb-4 inline-block">
                    ← Back to Marketplace
                </Link>

                {/* Success/Error Messages */}
                {flash?.success && (
                    <div className="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-lg">
                        {flash.error}
                    </div>
                )}

                <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
                    <div className="p-8 flex flex-col md:flex-row gap-8 border-b border-slate-200 dark:border-slate-700">
                        <img 
                            src={plugin.logo_path ? `/storage/${plugin.logo_path}` : '/images/default-plugin.png'} 
                            alt={plugin.name}
                            className="w-32 h-32 rounded-xl object-cover shadow-sm" 
                            onError={(e) => { e.target.src = '/images/default-plugin.png'; }}
                        />
                        <div className="flex-1">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <h1 className="text-3xl font-bold text-slate-900 dark:text-white mb-2">{plugin.name}</h1>
                                    <div className="flex items-center gap-2 mb-4">
                                        {renderStars(plugin.rating_avg || 0)}
                                        <span className="text-sm text-slate-600 dark:text-slate-400">
                                            {plugin.rating_avg ? plugin.rating_avg.toFixed(1) : '0.0'} ({plugin.rating_count || 0} {plugin.rating_count === 1 ? 'review' : 'reviews'})
                                        </span>
                                    </div>
                                </div>
                                {auth.user && (
                                    <button
                                        onClick={handleFavoriteToggle}
                                        disabled={favoriteLoading}
                                        className={`p-2 rounded-full transition-colors ${
                                            favoriteLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-700'
                                        }`}
                                        title={isFavorited ? 'Remove from favorites' : 'Add to favorites'}
                                    >
                                        <svg 
                                            className={`w-6 h-6 ${isFavorited ? 'fill-red-500 text-red-500' : 'fill-none text-slate-400'}`}
                                            stroke="currentColor" 
                                            strokeWidth="2" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                        </svg>
                                    </button>
                                )}
                            </div>
                            <p className="text-slate-600 dark:text-slate-300 mb-4">{plugin.description}</p>
                            
                            <div className="flex flex-wrap gap-3 text-sm">
                                <span className="bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full text-slate-800 dark:text-slate-200">
                                    v{plugin.version}
                                </span>
                                <span className="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full">
                                    {plugin.license_type}
                                </span>
                                <span className="bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-3 py-1 rounded-full">
                                    {plugin.compatibility}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Requirements Section */}
                    {plugin.requirements && (
                        <div className="p-8 border-b border-slate-200 dark:border-slate-700">
                            <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-3">Requirements</h3>
                            <div className="grid grid-cols-2 gap-4 text-sm">
                                {Object.entries(plugin.requirements).map(([key, value]) => (
                                    <div key={key}>
                                        <span className="text-slate-600 dark:text-slate-400 capitalize">{key}:</span>{' '}
                                        <span className="text-slate-900 dark:text-white font-medium">{value}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="p-8 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                        <div className="text-sm text-slate-500 dark:text-slate-400">
                            Downloads: <span className="font-bold text-slate-900 dark:text-white">{plugin.downloads}</span>
                        </div>
                        <button 
                            onClick={handleDownload}
                            className="bg-slate-900 dark:bg-teal-600 text-white px-6 py-3 rounded-lg font-bold hover:opacity-90 transition-opacity flex items-center gap-2"
                        >
                            <span>↓</span> Download / Install
                        </button>
                    </div>
                </div>

                {/* Review Submission Form */}
                {canReview && (
                    <div className="mt-8 bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8">
                        <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-4">
                            {userReview ? 'Update Your Review' : 'Write a Review'}
                        </h2>
                        <form onSubmit={handleReviewSubmit}>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Rating <span className="text-red-500">*</span>
                                </label>
                                {renderRatingSelector()}
                                {errors.rating && (
                                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.rating}</p>
                                )}
                            </div>

                            <div className="mb-4">
                                <label htmlFor="comment" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Comment (optional)
                                </label>
                                <textarea
                                    id="comment"
                                    value={data.comment}
                                    onChange={(e) => setData('comment', e.target.value)}
                                    maxLength={1000}
                                    rows={4}
                                    className="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                    placeholder="Share your experience with this plugin..."
                                />
                                <div className="mt-1 flex justify-between text-sm">
                                    <div>
                                        {errors.comment && (
                                            <p className="text-red-600 dark:text-red-400">{errors.comment}</p>
                                        )}
                                    </div>
                                    <span className="text-slate-500 dark:text-slate-400">
                                        {data.comment.length}/1000
                                    </span>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={processing || !data.rating}
                                className="bg-teal-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-teal-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {processing ? 'Submitting...' : (userReview ? 'Update Review' : 'Submit Review')}
                            </button>
                        </form>
                    </div>
                )}

                {!auth.user && (
                    <div className="mt-8 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-center">
                        <p className="text-blue-800 dark:text-blue-200">
                            <Link href={route('login')} className="font-medium underline hover:no-underline">
                                Sign in
                            </Link>
                            {' '}to write a review or favorite this plugin.
                        </p>
                    </div>
                )}

                {/* Reviews List */}
                <div className="mt-8">
                    <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-4">
                        Reviews {plugin.rating_count > 0 && `(${plugin.rating_count})`}
                    </h2>
                    {plugin.reviews && plugin.reviews.data && plugin.reviews.data.length > 0 ? (
                        <>
                            <div className="space-y-4">
                                {plugin.reviews.data.map(review => (
                                    <div key={review.id} className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                                        <div className="flex justify-between items-start mb-3">
                                            <div>
                                                <span className="font-bold text-slate-900 dark:text-white">{review.user.name}</span>
                                                <div className="mt-1">{renderStars(review.rating)}</div>
                                            </div>
                                            <span className="text-sm text-slate-500 dark:text-slate-400">
                                                {new Date(review.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        {review.comment && (
                                            <p className="text-slate-600 dark:text-slate-300 text-sm whitespace-pre-wrap">
                                                {review.comment}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>

                            {/* Pagination */}
                            {plugin.reviews.links && plugin.reviews.links.length > 3 && (
                                <div className="mt-6 flex justify-center gap-2">
                                    {plugin.reviews.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            preserveScroll
                                            className={`px-3 py-1 rounded text-sm ${
                                                link.active 
                                                    ? 'bg-teal-600 text-white' 
                                                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'
                                            } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </>
                    ) : (
                        <p className="text-slate-500 dark:text-slate-400 text-center py-8">
                            No reviews yet. Be the first to review this plugin!
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}