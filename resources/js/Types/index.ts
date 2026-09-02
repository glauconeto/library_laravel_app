export interface User {
    id: number;
    name: string;
    email: string;
    roles: { name: string }[];
}

export interface Book {
    id: number;
    title: string;
    author: string;
    genre: string;
    year: number;
    isbn: string;
    stock: number;
    loans_count: number;
    feedbacks_count?: number;
    description?: string | null;
    user_id?: number;
    created_at?: string;
    updated_at?: string;
    user?: User;
    loans?: Loan[];
    feedbacks?: Feedback[];
}

export interface Genre {
    id: number;
    name: string;
}

export interface Loan {
    id: number;
    user_id: number;
    book_id: number;
    loan_date: string;
    borrowed_at?: string;
    returned_at: string | null;
    due_date: string;
    status: string;
    is_overdue?: boolean;
    created_at?: string;
    updated_at?: string;
    book: Book;
    user: User;
}

export interface Feedback {
    id: number;
    user_id: number;
    book_id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    user: User;
    book: Book;
}

export interface PaginatedData<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
}

export interface PageProps extends Record<string, unknown> {
    auth: {
        user: User;
    };
    flash: {
        success?: string;
        error?: string;
    };
    errors: Record<string, string>;
}