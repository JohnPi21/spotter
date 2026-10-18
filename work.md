I want to rehaul my ci/cd pipeline. 
I created @validate-now.yml - kind of a playground to understand how everything works and interacts
Now that I have a much better idea of how things are working, I want to rehaul my old deploy.yml

First, I find having to test my code before pushing is a bad practice
If I just want to push to save my work because its the end of the day - I don't want to be forced to fix code errors
Some basic code quality like lint, pint is mostly fine
But having blocking things like php stan or failing tests just blocking my push is not ok
I want that removed - but because I don't want to lose code quality capabilities, we will dispalce that functionality to github runners

For a healty testing practice, I am thinking about these cases:
 - When pushing to a branch that is not main or prod - don't run anything automatically
 - When creating a PR to merge into main or prod - run the full test suite, meaning code quality lke lint, pint, phpstan, rectorphp if available, test (full testing, not just unit test, meaning span up a db and test end to end)
 - When updating that said PR with more work, run the tests again - I want the tests to be a condition of merge

This is as far as ci functionality goes for now. I know there could be cases where we just split code quality + unit tests from full end to end testing, but I feel like my application is small enough so we can jsut run full testing suite every time.
I just want to make a clarification, first we shall run the code quality - tests that don't use a db (including unit tests) - and only if these pass we boot up the db and run everything. This should save a lot of testing time if it jsut fails right after image build

Then for CD - for my app, I consider a simple CD. I think I'll drop the usage of prod branch for now. I will merge into main - that merge needs to pass tests ofc. Then I'll decide when main is ready for deployment by creating a tag and pushing it on main. That tag should be the drigger for deployment pipeline that will start, build, test, promote and start the deployment process. 

What do you think? Should I consider some extra things? Do you consider there are some gaps in my thought process? Lets discuss