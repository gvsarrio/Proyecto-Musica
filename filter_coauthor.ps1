foreach ($branch in (git branch -r --format='%(refname)' -- | Where-Object {$_ -match 'refs/heads/'})) {
    $branchName = $branch -replace 'refs/heads/', ''
    git checkout $branchName
    foreach ($commit in (git log --format='%H' --grep="Co-Authored-By: Claude" -- )) {
        git rebase -i "$($commit)^" -x "git commit --amend -m '$(git log -1 --format=%B $commit | Select-String -NotMatch 'Co-Authored' -Raw)'"
    }
}
