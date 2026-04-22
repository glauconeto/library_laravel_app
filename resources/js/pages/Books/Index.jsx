import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import Input from '../../Components/Input';
import PrimaryButton from '../../Components/PrimaryButton';

export default function BooksIndex({ books, filters, genres }) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedGenre, setSelectedGenre] = useState(filters.genre || '');
    const [availableOnly, setAvailableOnly] = useState(filters.available === 'true');

    const handleSearch = (e) => {
        setSearch(e.target.value);
    };

    const handleGenreChange = (e) => {
        setSelectedGenre(e.target.value);
    };

    const handleAvailableChange = (e) => {
        setAvailableOnly(e.target.checked);
    };

    const applyFilters = () => {
        const params = new URLSearchParams();
        
        if (search) params.append('search', search);
        if (selectedGenre) params.append('genre', selectedGenre);
        if (availableOnly) params.append('available', 'true');

        router.get(route('books.index'), params.toString());
    };

    const resetFilters = () => {
        setSearch('');
        setSelectedGenre('');
        setAvailableOnly(false);
        router.get(route('books.index'));
    };

    return (
        <AppLayout>
            <Head title="Livros" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                            Catálogo da Biblioteca
                        </h1>

                        {/* Filters */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Pesquisar
                                    </label>
                                    <Input
                                        type="text"
                                        value={search}
                                        onChange={handleSearch}
                                        placeholder="Pesquisar por título, autor, ISBN..."
                                        className="w-full"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Gênero
                                    </label>
                                    <select
                                        value={selectedGenre}
                                        onChange={handleGenreChange}
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    >
                                        <option value="">Todos os Gêneros</option>
                                        {genres.map((genre) => (
                                            <option key={genre} value={genre}>
                                                {genre}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="flex items-end">
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={availableOnly}
                                            onChange={handleAvailableChange}
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Apenas Disponíveis
                                        </span>
                                    </label>
                                </div>

                                <div className="flex items-end gap-2">
                                    <PrimaryButton onClick={applyFilters}>
                                        Aplicar Filtros
                                    </PrimaryButton>
                                    <button
                                        onClick={resetFilters}
                                        className="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition duration-150"
                                    >
                                        Limpar
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Create Book Button */}
                        <div className="flex justify-between items-center mb-6">
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                                {books.total} livros encontrados
                            </div>
                            <Link href={route('books.create')}>
                                <PrimaryButton>
                                    Adicionar Novo Livro
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    {/* Books Grid */}
                    {books.data.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {books.data.map((book) => (
                                <div
                                    key={book.id}
                                    className="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200"
                                >
                                    <div className="p-6">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            {book.title}
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400 mb-1">
                                            por {book.author}
                                        </p>
                                        <p className="text-sm text-gray-500 dark:text-gray-500 mb-2">
                                            {book.genre} • {book.year}
                                        </p>
                                        <div className="flex justify-between items-center mb-4">
                                            <span className="text-sm text-gray-600 dark:text-gray-400">
                                                Estoque: {book.stock}
                                            </span>
                                            <span className={`text-sm px-2 py-1 rounded ${
                                                book.loans_count < book.stock
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            }`}>
                                                {book.loans_count < book.stock ? 'Disponível' : 'Todos Emprestados'}
                                            </span>
                                        </div>
                                        {book.description && (
                                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                                                {book.description}
                                            </p>
                                        )}
                                        <div className="flex gap-2">
                                            <Link
                                                href={route('books.show', book.id)}
                                                className="flex-1 text-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150"
                                            >
                                                Ver Detalhes
                                            </Link>
                                            <Link
                                                href={route('books.edit', book.id)}
                                                className="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-150"
                                            >
                                                Editar
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-8">
                                <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    Nenhum livro encontrado
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-6">
                                    Tente ajustar seus filtros ou adicione um novo livro à biblioteca.
                                </p>
                                <Link href={route('books.create')}>
                                    <PrimaryButton>
                                        Adicionar Novo Livro
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    )}

                    {/* Pagination */}
                    {books.links && books.links.length > 3 && (
                        <div className="mt-8 flex justify-center">
                            <div className="flex gap-2">
                                {books.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`px-3 py-2 rounded-md text-sm ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
