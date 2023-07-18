import React from "react";
import { Link, router } from "@inertiajs/react";
import {
    PencilSquareIcon,
    TrashIcon,
    MagnifyingGlassIcon,
} from "@heroicons/react/24/outline";
import TextInput from "@/components/TextInput";
import { pickBy } from "lodash";
import Swal from "sweetalert2";

function Table({ divisi, handleEdit, handleDelete }) {
    const [isLoading, setIsLoading] = React.useState(false);
    const perpage = React.useRef(divisi.per_page);
    const [search, setSearch] = React.useState("");

    const handlePerPageChange = (e) => {
        perpage.current = e.target.value;
        getData();
    };

    const handleSearch = (e) => {
        e.preventDefault();
        getData();
    };

    const getData = () => {
        setIsLoading(true);
        router.get(
            route("divisi.index"),
            pickBy({ perpage: perpage.current, search }),
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => setIsLoading(false),
            }
        );
    };

    return (
        <>
            <div className="flex items-center justify-between py-2">
                <div>
                    <select
                        name="perpage"
                        id="perpage"
                        value={perpage.current}
                        onChange={handlePerPageChange}
                        className="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-orange-600 focus:border-orange-600 focus:ring-2 block w-20 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500 "
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <form
                    className="flex items-center gap-x-2"
                    onSubmit={handleSearch}
                >
                    <TextInput
                        onChange={(e) => setSearch(e.target.value)}
                        value={search}
                        type="search"
                        name="search"
                        id="search"
                        placeholder="Search..."
                    />
                    <button
                        type="submit"
                        className="p-2.5 text-white bg-orange-400 rounded-md hover:bg-opacity-80"
                    >
                        <MagnifyingGlassIcon className="w-5 h-5" />
                    </button>
                </form>
            </div>
            <table className="w-full mt-2 text-sm text-left text-gray-500 dark:text-gray-400">
                <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr className="text-white bg-gray-500 ">
                        <th className="w-10 px-6 py-3 text-center">#</th>
                        <th className="px-6 py-3">Nama Divisi</th>
                        <th className="w-32 px-6 py-3 text-center">@</th>
                    </tr>
                </thead>
                <tbody>
                    {isLoading ? (
                        <tr>
                            <td colSpan={3} className="px-6 py-3 text-center">
                                Loading...
                            </td>
                        </tr>
                    ) : (
                        divisi.data.map((item, index) => (
                            <tr
                                key={index}
                                className="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                            >
                                <td className="px-6 py-3 text-center">
                                    {divisi.from + index}
                                </td>
                                <td className="px-6 py-3">
                                    {item.nama_divisi}
                                </td>
                                <td className="flex items-center justify-center gap-1 px-6 py-3 text-white">
                                    <button
                                        onClick={() => handleEdit(item)}
                                        type="button"
                                        className="flex items-center justify-center p-1 transition-all duration-200 bg-blue-500 rounded-sm shadow-lg hover:bg-opacity-80"
                                    >
                                        <PencilSquareIcon className="w-5 h-5" />
                                    </button>
                                    <button
                                        onClick={() => handleDelete(item.id)}
                                        type="button"
                                        className="flex items-center justify-center p-1 transition-all duration-200 bg-red-500 rounded-sm shadow hover:bg-opacity-80"
                                    >
                                        <TrashIcon className="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
            <div className="flex items-center justify-between mt-5">
                <p>
                    Showing
                    <span className="font-bold"> {divisi.from}</span> to
                    <span className="font-bold"> {divisi.to} </span>
                    total
                    <span className="font-bold"> {divisi.total}</span>
                </p>

                <div className="flex items-center justify-center gap-2">
                    {divisi.links.map((item, index) => {
                        return (
                            <Link
                                href={item.url}
                                key={index}
                                className="px-3 py-1 text-white bg-orange-400 rounded-md hover:bg-opacity-80 text-md"
                                preserveScroll
                                preserveState
                            >
                                <div
                                    dangerouslySetInnerHTML={{
                                        __html: item.label,
                                    }}
                                />
                            </Link>
                        );
                    })}
                </div>
            </div>
        </>
    );
}

export default Table;
