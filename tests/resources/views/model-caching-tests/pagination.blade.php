<!DOCTYPE html>
<html>
    <body>
        <table>

            @foreach ($books as $book)
                <tr>
                    <td>
                        {{ $book->id }}
                    </td>
                    <td>
                        {{ $book->title }}
                    </td>
                </tr>
            @endforeach

        </table>

        {{ $books->links() }}

    </body>
</html>
